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
        $date = now();
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
        $clockIn = now();
        $status = $this->statusFor($clockIn, $assignment);
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
            'geofence_validated' => true,
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
        $date = now();
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
            'clock_out_at' => now(),
            'out_latitude' => $input['latitude'],
            'out_longitude' => $input['longitude'],
            'geofence_distance_meters' => $distance,
            'geofence_validated' => true,
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
        $attendance = Attendance::query()
            ->with('shift')
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        return response()->json(['data' => [
            'clock_in' => optional($attendance?->clock_in_at)->format('H:i'),
            'clock_out' => optional($attendance?->clock_out_at)->format('H:i'),
            'shift' => $attendance?->shift?->name ?? 'Belum ada shift',
            'work_hours' => $this->workHours($attendance),
            'is_clocked_in' => (bool) $attendance?->clock_in_at,
            'is_clocked_out' => (bool) $attendance?->clock_out_at,
        ]]);
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

    private function statusFor(Carbon $clockIn, $assignment): string
    {
        $shift = $assignment?->shift;
        if (!$shift || !$shift->clock_in_time) {
            return 'present';
        }

        $expected = Carbon::parse($clockIn->toDateString() . ' ' . $shift->clock_in_time)
            ->addMinutes((int) $shift->grace_in_minutes);
        return $clockIn->greaterThan($expected) ? 'late' : 'present';
    }

    private function workHours(?Attendance $attendance): string
    {
        if (!$attendance?->clock_in_at) {
            return '00:00';
        }

        $end = $attendance->clock_out_at ?: now();
        $minutes = max(0, $attendance->clock_in_at->diffInMinutes($end));
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
