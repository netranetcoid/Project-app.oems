<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OvertimeAttendance;
use App\Services\Attendance\AttendanceProofService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** Presensi lembur mandiri sesudah presensi reguler selesai. */
class OvertimeAttendanceController extends Controller
{
    public function __construct(private AttendanceProofService $proofs) {}

    public function checkIn(Request $request)
    {
        $employee = $this->proofs->employeeFor($request->user());
        $now = now();
        $normal = $this->normalAttendance($employee->company_id, $employee->id, $now);
        if (! $normal?->clock_out_at) {
            throw ValidationException::withMessages(['overtime' => ['Presensi pulang reguler wajib dilakukan sebelum mulai lembur.']]);
        }
        $assignment = $this->proofs->assignment($employee, $now);
        $policy = $this->policy($employee, $assignment);
        if (! $policy['allow_overtime']) throw ValidationException::withMessages(['overtime' => ['Lembur tidak diizinkan pada shift ini.']]);
        if ($now->lt($normal->clock_out_at->copy()->addMinutes($policy['wait_minutes']))) {
            throw ValidationException::withMessages(['overtime' => ["Lembur dapat dimulai {$policy['wait_minutes']} menit setelah presensi pulang."]]);
        }
        $data = $this->validatedProof($request, $policy['selfie_required']);
        $existing = OvertimeAttendance::query()->where('company_id', $employee->company_id)->where('employee_id', $employee->id)->whereDate('date', $now)->first();
        if ($existing?->clock_in_at) throw ValidationException::withMessages(['overtime' => ['Presensi masuk lembur hari ini sudah tercatat.']]);
        $distance = $this->proofs->assertGeofence($data, $policy);
        $overtime = $existing ?: new OvertimeAttendance();
        $overtime->fill([
            'company_id' => $employee->company_id, 'employee_id' => $employee->id, 'attendance_id' => $normal->id,
            'attendance_shift_id' => $assignment?->attendance_shift_id, 'date' => $now->toDateString(), 'clock_in_at' => $now,
            'in_latitude' => $data['latitude'], 'in_longitude' => $data['longitude'], 'gps_accuracy_meters' => $data['accuracy'] ?? null,
            'geofence_distance_meters' => $distance, 'geofence_validated' => (bool) $policy['geofence_required'], 'in_photo' => $this->storeProof($request, $employee->company_id, $employee->id, $now, 'in'),
            'client_occurred_at' => isset($data['occurred_at']) ? Carbon::parse($data['occurred_at']) : null,
            'retention_until' => $this->proofs->retentionUntil($policy), 'device_id' => $data['device_id'] ?? $request->header('X-Device-Id'), 'notes' => $data['note'] ?? null,
        ])->save();
        return response()->json(['data' => $this->payload($overtime->fresh(), $policy['max_minutes']), 'message' => 'Presensi masuk lembur berhasil dicatat.'], 201);
    }

    public function checkOut(Request $request)
    {
        $employee = $this->proofs->employeeFor($request->user()); $now = now();
        $assignment = $this->proofs->assignment($employee, $now); $policy = $this->policy($employee, $assignment);
        $overtime = OvertimeAttendance::query()->where('company_id', $employee->company_id)->where('employee_id', $employee->id)->whereDate('date', $now)->first();
        if (! $overtime?->clock_in_at) throw ValidationException::withMessages(['overtime' => ['Presensi masuk lembur belum tercatat.']]);
        if ($overtime->clock_out_at) throw ValidationException::withMessages(['overtime' => ['Presensi keluar lembur sudah tercatat.']]);
        $data = $this->validatedProof($request, $policy['selfie_required']); $distance = $this->proofs->assertGeofence($data, $policy);
        // Checkout tetap diterima bila karyawan sedikit terlambat menekan
        // tombol, namun jam terhitung dikunci di batas harian yang disetel HR.
        $countedOut = $now->min($overtime->clock_in_at->copy()->addMinutes($policy['max_minutes']));
        $overtime->update(['clock_out_at' => $countedOut, 'out_latitude' => $data['latitude'], 'out_longitude' => $data['longitude'],
            'gps_accuracy_meters' => $data['accuracy'] ?? $overtime->gps_accuracy_meters, 'geofence_distance_meters' => $distance, 'geofence_validated' => (bool) $policy['geofence_required'],
            'out_photo' => $this->storeProof($request, $employee->company_id, $employee->id, $now, 'out'),
            'client_occurred_at' => isset($data['occurred_at']) ? Carbon::parse($data['occurred_at']) : $overtime->client_occurred_at,
            'device_id' => $data['device_id'] ?? $request->header('X-Device-Id'), 'notes' => $data['note'] ?? $overtime->notes]);
        return response()->json(['data' => $this->payload($overtime->fresh(), $policy['max_minutes']), 'message' => 'Presensi keluar lembur berhasil dicatat.']);
    }

    public function today(Request $request)
    {
        $employee = $this->proofs->employeeFor($request->user()); $assignment = $this->proofs->assignment($employee, now()); $policy = $this->policy($employee, $assignment);
        $item = OvertimeAttendance::query()->where('company_id', $employee->company_id)->where('employee_id', $employee->id)->whereDate('date', today())->first();
        return response()->json(['data' => $this->payload($item, $policy['max_minutes'])]);
    }

    public function history(Request $request)
    {
        $employee = $this->proofs->employeeFor($request->user());
        $items = OvertimeAttendance::query()->where('company_id', $employee->company_id)->where('employee_id', $employee->id)->latest('date')->limit(90)->get()
            ->map(fn (OvertimeAttendance $item) => $this->payload($item, 180));
        return response()->json(['data' => ['items' => $items]]);
    }

    public function proof(Request $request, OvertimeAttendance $overtime, string $direction)
    {
        $employee = $this->proofs->employeeFor($request->user());
        abort_unless((int) $overtime->employee_id === (int) $employee->id && (int) $overtime->company_id === (int) $employee->company_id, 403);
        abort_unless(in_array($direction, ['in', 'out'], true), 404); abort_if($overtime->retention_until && $overtime->retention_until->isPast(), 410);
        $path = $direction === 'in' ? $overtime->in_photo : $overtime->out_photo;
        abort_unless($path && Storage::disk('public')->exists($path), 404);
        return Storage::disk('public')->response($path, null, ['Cache-Control' => 'private, no-store, max-age=0']);
    }

    private function normalAttendance(int $companyId, int $employeeId, Carbon $date): ?Attendance { return Attendance::query()->where('company_id', $companyId)->where('employee_id', $employeeId)->whereDate('date', $date)->first(); }
    private function policy($employee, $assignment): array { $base = $this->proofs->policy($employee, $assignment); $shift = $assignment?->shift; return [...$base, 'allow_overtime' => (bool) ($shift?->allow_overtime ?? true), 'wait_minutes' => max(0, (int) ($shift?->overtime_after_minutes ?? 30)), 'max_minutes' => max(1, (int) ($shift?->overtime_max_minutes ?? 180))]; }
    private function validatedProof(Request $request, bool $required): array { $data = $request->validate(['latitude' => ['required','numeric','between:-90,90'], 'longitude' => ['required','numeric','between:-180,180'], 'accuracy' => ['nullable','numeric','min:0','max:1000'], 'occurred_at' => ['nullable','date'], 'device_id' => ['nullable','string','max:120'], 'note' => ['nullable','string','max:2000'], 'selfie' => ['nullable','image','mimes:jpeg,png,jpg,webp','max:4096']]); if ($required && ! $request->hasFile('selfie')) throw ValidationException::withMessages(['selfie' => ['Selfie wajib diunggah untuk lembur.']]); return $data; }
    private function storeProof(Request $request, int $companyId, int $employeeId, Carbon $date, string $direction): ?string { $file = $request->file('selfie'); return $file ? $file->store("overtime/{$companyId}/{$employeeId}/{$date->toDateString()}/{$direction}", 'public') : null; }
    private function payload(?OvertimeAttendance $item, int $maxMinutes): array { $minutes = $item?->clock_in_at ? max(0, $item->clock_in_at->diffInMinutes($item->clock_out_at ?: now())) : 0; return ['id' => $item?->id, 'clock_in' => $item?->clock_in_at?->format('H:i'), 'clock_out' => $item?->clock_out_at?->format('H:i'), 'clock_in_at' => $item?->clock_in_at?->toIso8601String(), 'clock_out_at' => $item?->clock_out_at?->toIso8601String(), 'work_minutes' => $minutes, 'work_hours' => sprintf('%02d:%02d', intdiv($minutes,60),$minutes%60), 'max_minutes' => $maxMinutes, 'in_selfie_url' => $item?->in_photo ? url("/api/v1/overtime/history/{$item->id}/proof/in") : null, 'out_selfie_url' => $item?->out_photo ? url("/api/v1/overtime/history/{$item->id}/proof/out") : null]; }
}
