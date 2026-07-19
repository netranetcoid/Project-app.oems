<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\Attendance\AttendanceProofService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceProofService $proofs
    ) {
    }

    public function checkIn(Request $request)
    {
        $user = $request->user();
        $employee = $this->proofs->employeeFor($user);
        // Database menyimpan instant dalam UTC, sementara tanggal presensi
        // dan aturan shift mengikuti timezone operasional perusahaan.
        $timezone = $this->timezoneFor($employee);
        $date = now($timezone);
        $assignment = $this->proofs->assignment($employee, $date);
        $policy = $this->proofs->policy($employee, $assignment);
        $input = $this->validatedProof($request, $policy['selfie_required']);
        $distance = $this->proofs->assertGeofence($input, $policy);

        $existing = Attendance::query()
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->whereDate('date', $date->toDateString())
            ->first();
        if ($existing?->clock_in_at) {
            throw ValidationException::withMessages([
                'attendance' => ['Presensi masuk hari ini sudah tercatat.'],
            ]);
        }

        $photoPath = $this->storeProof($request, $employee->company_id, $employee->id, $date);
        $clockIn = now('UTC');
        $status = $this->statusFor($clockIn, $assignment, $timezone);
        $payload = [
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'attendance_shift_id' => $assignment?->attendance_shift_id,
            'date' => $date->toDateString(),
            'clock_in_at' => $clockIn,
            'in_latitude' => $input['latitude'],
            'in_longitude' => $input['longitude'],
            'gps_accuracy_meters' => $input['accuracy'] ?? null,
            'geofence_distance_meters' => $distance,
            'geofence_validated' => (bool) $policy['geofence_required'],
            'in_photo' => $photoPath,
            'status' => $status,
            'notes' => $input['note'] ?? null,
            'device_id' => $input['device_id'] ?? $request->header('X-Device-Id'),
            'client_occurred_at' => $this->clientTime($input['occurred_at'] ?? null),
            'retention_until' => $this->proofs->retentionUntil($policy),
        ];

        $attendance = $existing
            ? tap($existing)->update($payload)
            : Attendance::create($payload);

        return response()->json([
            'status' => 'success',
            'message' => 'Presensi masuk berhasil dicatat.',
            'data' => $attendance->fresh(['shift']),
        ], 201);
    }

    public function checkOut(Request $request)
    {
        $user = $request->user();
        $employee = $this->proofs->employeeFor($user);
        $timezone = $this->timezoneFor($employee);
        $date = now($timezone);
        $assignment = $this->proofs->assignment($employee, $date);
        $policy = $this->proofs->policy($employee, $assignment);
        $input = $this->validatedProof($request, $policy['selfie_required']);
        $distance = $this->proofs->assertGeofence($input, $policy);
        $attendance = Attendance::query()
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->whereDate('date', $date->toDateString())
            ->first();

        if (!$attendance?->clock_in_at) {
            throw ValidationException::withMessages([
                'attendance' => ['Presensi masuk belum tercatat.'],
            ]);
        }
        if ($attendance->clock_out_at) {
            throw ValidationException::withMessages([
                'attendance' => ['Presensi pulang hari ini sudah tercatat.'],
            ]);
        }

        $attendance->update([
            'clock_out_at' => now('UTC'),
            'out_latitude' => $input['latitude'],
            'out_longitude' => $input['longitude'],
            'geofence_distance_meters' => $distance,
            'geofence_validated' => (bool) $policy['geofence_required'],
            'out_photo' => $this->storeProof($request, $employee->company_id, $employee->id, $date),
            'notes' => $input['note'] ?? $attendance->notes,
            'device_id' => $input['device_id'] ?? $request->header('X-Device-Id'),
            'client_occurred_at' => $this->clientTime($input['occurred_at'] ?? null),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Presensi pulang berhasil dicatat.',
            'data' => $attendance->fresh(['shift']),
        ]);
    }

    public function today(Request $request)
    {
        $employee = $this->proofs->employeeFor($request->user());
        $timezone = $this->timezoneFor($employee);
        $businessNow = now($timezone);
        $attendance = Attendance::query()
            ->with('shift')
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->whereDate('date', $businessNow->toDateString())
            ->first();

        return response()->json(['data' => [
            'clock_in' => $this->formatTime($attendance?->clock_in_at, $timezone),
            'clock_out' => $this->formatTime($attendance?->clock_out_at, $timezone),
            // Timestamp ISO membuat APK dapat menghitung durasi berjalan tanpa
            // perlu terus-menerus memanggil server setiap menit.
            'clock_in_at' => optional($attendance?->clock_in_at)->toIso8601String(),
            'clock_out_at' => optional($attendance?->clock_out_at)->toIso8601String(),
            'shift' => $attendance?->shift?->name ?? 'Belum ada shift',
            'work_hours' => $this->workHours($attendance),
            'work_minutes' => $this->workMinutes($attendance),
            'is_clocked_in' => (bool) $attendance?->clock_in_at,
            'is_clocked_out' => (bool) $attendance?->clock_out_at,
        ]]);
    }

    /**
     * Policy is read-only guidance for the mobile capture screen. The server
     * still validates GPS and retention again during check-in/check-out.
     */
    public function policy(Request $request)
    {
        $employee = $this->proofs->employeeFor($request->user());
        $assignment = $this->proofs->assignment($employee, now($this->timezoneFor($employee)));
        $policy = $this->proofs->policy($employee, $assignment);
        return response()->json(['data' => [
            ...$policy,
            'office_name' => $policy['location_name'],
            'shift_name' => $assignment?->shift?->name ?? 'Belum ada shift',
        ]]);
    }

    /**
     * History exposes only the employee's own evidence URL. Raw coordinates
     * remain private, while the photo itself is served by an authenticated
     * endpoint below (never as a guessable public-storage URL).
     */
    public function history(Request $request)
    {
        $employee = $this->proofs->employeeFor($request->user());
        $timezone = $this->timezoneFor($employee);
        $items = Attendance::query()
            ->with('shift')
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->latest('date')
            ->limit(90)
            ->get()
            ->map(fn (Attendance $attendance): array => [
                'id' => $attendance->id,
                'date' => optional($attendance->date)->toDateString(),
                'clock_in' => $this->formatTime($attendance->clock_in_at, $timezone),
                'clock_out' => $this->formatTime($attendance->clock_out_at, $timezone),
                'shift' => $attendance->shift?->name ?? 'Tanpa shift',
                'status' => $attendance->status ?? 'present',
                'work_hours' => $this->workHours($attendance),
                'in_selfie_url' => $attendance->in_photo
                    ? url("/api/v1/attendance/history/{$attendance->id}/proof/in") : null,
                'out_selfie_url' => $attendance->out_photo
                    ? url("/api/v1/attendance/history/{$attendance->id}/proof/out") : null,
            ]);

        return response()->json(['items' => $items]);
    }

    /**
     * Streams a selfie only to its employee. HR review has a separate web
     * route; this endpoint deliberately never returns GPS or another user's
     * proof. Retention policy is enforced even when an old URL is cached.
     */
    public function historyProof(Request $request, Attendance $attendance, string $direction)
    {
        $employee = $this->proofs->employeeFor($request->user());
        abort_unless(
            (int) $attendance->company_id === (int) $employee->company_id
                && (int) $attendance->employee_id === (int) $employee->id,
            403
        );
        abort_unless(in_array($direction, ['in', 'out'], true), 404);
        abort_if($attendance->retention_until && $attendance->retention_until->isPast(), 410,
            'Masa simpan bukti presensi telah berakhir.');

        $path = $direction === 'in' ? $attendance->in_photo : $attendance->out_photo;
        abort_unless($path && Storage::disk('public')->exists($path), 404, 'Bukti selfie tidak tersedia.');

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    private function validatedProof(Request $request, bool $selfieRequired): array
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'occurred_at' => ['nullable', 'date'],
            'device_id' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:2000'],
            'selfie' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        if ($selfieRequired && !$request->hasFile('selfie') && !$request->hasFile('photo')) {
            throw ValidationException::withMessages([
                'selfie' => ['Selfie wajib diunggah untuk presensi.'],
            ]);
        }

        return $data;
    }

    private function storeProof(Request $request, int $companyId, int $employeeId, Carbon $date): ?string
    {
        $file = $request->file('selfie') ?: $request->file('photo');
        if (!$file) {
            return null;
        }

        return $file->store("attendance/{$companyId}/{$employeeId}/{$date->toDateString()}", 'public');
    }

    private function clientTime(?string $value): ?Carbon
    {
        return $value ? Carbon::parse($value) : null;
    }

    private function statusFor(Carbon $clockIn, $assignment, string $timezone): string
    {
        $shift = $assignment?->shift;
        if (!$shift || !$shift->clock_in_time) {
            return 'present';
        }

        $expected = Carbon::parse($clockIn->copy()->setTimezone($timezone)->toDateString() . ' ' . $shift->clock_in_time, $timezone)
            ->addMinutes((int) $shift->grace_in_minutes);
        return $clockIn->copy()->setTimezone($timezone)->greaterThan($expected) ? 'late' : 'present';
    }

    private function workHours(?Attendance $attendance): string
    {
        $minutes = $this->workMinutes($attendance);
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    private function workMinutes(?Attendance $attendance): int
    {
        if (! $attendance?->clock_in_at) {
            return 0;
        }

        return max(0, $attendance->clock_in_at->diffInMinutes($attendance->clock_out_at ?: now('UTC')));
    }

    /** Business timezone is per company, with Jakarta as safe operational fallback. */
    private function timezoneFor($employee): string
    {
        $timezone = (string) ($employee->company?->timezone ?: 'Asia/Jakarta');

        return in_array($timezone, timezone_identifiers_list(), true) ? $timezone : 'Asia/Jakarta';
    }

    /** Never expose raw UTC clock values to a mobile employee. */
    private function formatTime(?Carbon $value, string $timezone): ?string
    {
        return $value?->copy()->setTimezone($timezone)->format('H:i');
    }
}
