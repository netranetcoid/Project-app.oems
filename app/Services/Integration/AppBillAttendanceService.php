<?php

namespace App\Services\Integration;

use App\Models\Attendance;
use App\Models\AttendanceShift;
use App\Models\Company;
use App\Models\Employee;
use App\Models\IntegrationConnection;
use App\Models\IntegrationInbox;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppBillAttendanceService
{
    public function __construct(private AppBillIntegrationService $outbox)
    {
    }

    public function queueAttendance(Attendance $attendance, string $eventType): void
    {
        if (! Schema::hasTable('integration_outbox')) {
            return;
        }

        $attendance->loadMissing(['employee.division', 'shift']);
        if (! $attendance->employee) {
            return;
        }

        $version = max(1, (int) ($attendance->sync_version ?? 1));
        $this->outbox->queueEvent(
            (int) $attendance->company_id,
            $eventType,
            $this->attendancePayload($attendance),
            "appbill:attendance:{$attendance->source_record_id}:v{$version}",
            Attendance::class,
            $attendance->id
        );
    }

    public function queueEmployee(Employee $employee, string $eventType): void
    {
        if (! Schema::hasTable('integration_outbox') || blank($employee->employee_no)) {
            return;
        }

        $employee->loadMissing('division');
        $revision = optional($employee->updated_at)->format('YmdHisv') ?: now()->format('YmdHisv');
        $this->outbox->queueEvent(
            (int) $employee->company_id,
            $eventType,
            $this->employeePayload($employee),
            "appbill:employee:{$employee->id}:{$revision}",
            Employee::class,
            $employee->id
        );
    }

    public function employees(Company $company, int $perPage): LengthAwarePaginator
    {
        return Employee::query()
            ->with('division')
            ->forCompany((int) $company->id)
            ->orderBy('employee_no')
            ->paginate(max(1, min($perPage, 100)));
    }

    public function attendances(Company $company, string $startDate, string $endDate, int $perPage): LengthAwarePaginator
    {
        return Attendance::query()
            ->with(['employee.division', 'shift'])
            ->where('company_id', $company->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('id')
            ->paginate(max(1, min($perPage, 100)));
    }

    public function shifts(Company $company, int $perPage): LengthAwarePaginator
    {
        return AttendanceShift::query()
            ->company((int) $company->id)
            ->active()
            ->orderBy('code')
            ->paginate(max(1, min($perPage, 100)));
    }

    public function upsertInbound(Company $company, IntegrationConnection $connection, array $input, string $requestId): array
    {
        $data = Arr::get($input, 'data', $input);
        $data = $this->validateInboundPayload($data);
        $eventId = (string) (Arr::get($input, 'event_id') ?: $requestId);
        $idempotencyKey = (string) (Arr::get($input, 'idempotency_key') ?: $requestId);
        $inbox = $this->startInbox($company, $connection, $eventId, $idempotencyKey, $input);

        if ($inbox->status === 'processed') {
            return ['status' => 'synced', 'sync_id' => "INBOX-{$inbox->id}", 'duplicate' => true];
        }

        $employee = Employee::query()
            ->forCompany((int) $company->id)
            ->where('employee_no', $data['employee_code'])
            ->first();
        if (! $employee) {
            $inbox->update(['status' => 'pending_mapping', 'last_error' => 'employee_code belum dipetakan.']);
            throw ValidationException::withMessages([
                'employee_code' => ['Kode karyawan belum dipetakan ke AppOEMS.'],
            ]);
        }

        $attendance = Attendance::query()
            ->where('company_id', $company->id)
            ->where('source_record_id', $data['source_record_id'])
            ->first();
        if ($attendance && (int) $attendance->sync_version >= (int) $data['version']) {
            $inbox->update(['status' => 'processed', 'processed_at' => now()]);
            return ['status' => 'synced', 'sync_id' => "INBOX-{$inbox->id}", 'duplicate' => true];
        }

        $shift = filled($data['shift_code'] ?? null)
            ? AttendanceShift::query()->company((int) $company->id)->where('code', $data['shift_code'])->first()
            : null;
        $payload = [
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'attendance_shift_id' => $shift?->id,
            'date' => $data['attendance_date'],
            'clock_in_at' => $this->asCarbon($data['check_in'] ?? null),
            'clock_out_at' => $this->asCarbon($data['check_out'] ?? null),
            'status' => $data['status'],
            'approval_status' => $data['approval_status'],
            'source_record_id' => $data['source_record_id'],
            'sync_version' => (int) $data['version'],
            'sync_status' => 'synced',
            'sync_updated_at' => $this->asCarbon($data['updated_at']),
            'change_reason' => $data['change_reason'] ?? null,
            'is_cancelled' => false,
        ];

        // Inbound tidak di-observe agar AppBill tidak menerima echo dari event
        // yang baru dikirimnya sendiri. Histori tetap tersimpan di inbox.
        if ($attendance) {
            Attendance::withoutEvents(fn () => $attendance->update($payload));
        } else {
            $attendance = Attendance::withoutEvents(fn () => Attendance::create($payload));
        }

        $inbox->update(['status' => 'processed', 'processed_at' => now(), 'last_error' => null]);
        return ['status' => 'synced', 'sync_id' => "INBOX-{$inbox->id}", 'attendance' => $attendance->fresh(['employee.division', 'shift'])];
    }

    public function cancelInbound(Company $company, IntegrationConnection $connection, string $sourceRecordId, array $input, string $requestId): array
    {
        $eventId = (string) (Arr::get($input, 'event_id') ?: $requestId);
        $inbox = $this->startInbox($company, $connection, $eventId, $requestId, $input);
        if ($inbox->status === 'processed') {
            return ['status' => 'synced', 'sync_id' => "INBOX-{$inbox->id}", 'duplicate' => true];
        }

        $attendance = Attendance::query()
            ->where('company_id', $company->id)
            ->where('source_record_id', $sourceRecordId)
            ->firstOrFail();
        Attendance::withoutEvents(function () use ($attendance, $input): void {
            $attendance->update([
                'is_cancelled' => true,
                'status' => 'incomplete',
                'approval_status' => 'rejected',
                'sync_status' => 'synced',
                'sync_version' => max(1, (int) $attendance->sync_version + 1),
                'sync_updated_at' => now(),
                'change_reason' => (string) (Arr::get($input, 'change_reason') ?: 'Dibatalkan melalui AppBill.'),
            ]);
        });
        $inbox->update(['status' => 'processed', 'processed_at' => now()]);
        return ['status' => 'synced', 'sync_id' => "INBOX-{$inbox->id}"];
    }

    public function employeePayload(Employee $employee): array
    {
        return [
            'employee_code' => $employee->employee_no,
            'name' => $employee->name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'division' => $employee->division?->name,
            'division_type' => $employee->division?->type,
            'employment_status' => $employee->work_status,
            'updated_at' => optional($employee->updated_at)->toIso8601String(),
        ];
    }

    public function attendancePayload(Attendance $attendance): array
    {
        return [
            'source_record_id' => $attendance->source_record_id,
            'employee_code' => $attendance->employee?->employee_no,
            'attendance_date' => optional($attendance->date)->toDateString(),
            'check_in' => optional($attendance->clock_in_at)->toIso8601String(),
            'check_out' => optional($attendance->clock_out_at)->toIso8601String(),
            'status' => $attendance->status,
            'late_minutes' => $this->lateMinutes($attendance),
            'work_minutes' => $this->workMinutes($attendance),
            'shift_code' => $attendance->shift?->code,
            'timezone' => $attendance->employee?->company?->timezone ?? 'Asia/Jakarta',
            'approval_status' => $attendance->approval_status ?? 'approved',
            'version' => max(1, (int) ($attendance->sync_version ?? 1)),
            'updated_at' => optional($attendance->sync_updated_at ?: $attendance->updated_at)->toIso8601String(),
            'is_cancelled' => (bool) $attendance->is_cancelled,
        ];
    }

    public function shiftPayload(AttendanceShift $shift, string $timezone): array
    {
        $breakMinutes = 0;
        if ($shift->break_start && $shift->break_end) {
            $breakMinutes = Carbon::parse($shift->break_start)->diffInMinutes(Carbon::parse($shift->break_end));
        }
        return [
            'shift_code' => $shift->code,
            'shift_name' => $shift->name,
            'start_time' => substr((string) $shift->clock_in_time, 0, 5),
            'end_time' => substr((string) $shift->clock_out_time, 0, 5),
            'break_minutes' => $breakMinutes,
            'timezone' => $timezone,
        ];
    }

    private function validateInboundPayload(array $data): array
    {
        $allowedStatuses = ['present', 'late', 'absent', 'leave', 'sick', 'permission', 'holiday', 'off', 'incomplete'];
        $approvalStatuses = ['draft', 'submitted', 'approved', 'rejected', 'corrected'];
        $validator = validator($data, [
            'source_record_id' => ['required', 'string', 'max:120'],
            'employee_code' => ['required', 'string', 'max:100'],
            'attendance_date' => ['required', 'date'],
            'check_in' => ['nullable', 'date'],
            'check_out' => ['nullable', 'date', 'after_or_equal:check_in'],
            'status' => ['required', 'in:' . implode(',', $allowedStatuses)],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'work_minutes' => ['nullable', 'integer', 'min:0'],
            'shift_code' => ['nullable', 'string', 'max:50'],
            'timezone' => ['nullable', 'timezone'],
            'approval_status' => ['required', 'in:' . implode(',', $approvalStatuses)],
            'version' => ['required', 'integer', 'min:1'],
            'updated_at' => ['required', 'date'],
            'change_reason' => ['nullable', 'string', 'max:2000'],
        ]);
        return $validator->validate();
    }

    private function startInbox(Company $company, IntegrationConnection $connection, string $eventId, string $idempotencyKey, array $payload): IntegrationInbox
    {
        $hash = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $existing = IntegrationInbox::query()
            ->forCompany((int) $company->id)
            ->where('provider', 'appbill')
            ->where(function ($query) use ($eventId, $idempotencyKey): void {
                $query->where('external_event_id', $eventId)
                    ->orWhere('idempotency_key', $idempotencyKey);
            })
            ->first();
        if ($existing) {
            if (! hash_equals((string) $existing->payload_hash, $hash)) {
                abort(response()->json(['success' => false, 'message' => 'Event ID digunakan untuk payload berbeda.'], 409));
            }
            return $existing;
        }

        return DB::transaction(fn () => IntegrationInbox::create([
            'company_id' => $company->id,
            'integration_connection_id' => $connection->id,
            'provider' => 'appbill',
            'external_event_id' => $eventId,
            'idempotency_key' => $idempotencyKey,
            'payload_hash' => $hash,
            'status' => 'received',
            'received_at' => now(),
        ]));
    }

    private function asCarbon(?string $value): ?Carbon
    {
        return filled($value) ? Carbon::parse($value) : null;
    }

    private function workMinutes(Attendance $attendance): int
    {
        return $attendance->clock_in_at && $attendance->clock_out_at
            ? max(0, $attendance->clock_in_at->diffInMinutes($attendance->clock_out_at))
            : 0;
    }

    private function lateMinutes(Attendance $attendance): int
    {
        if (! $attendance->clock_in_at || ! $attendance->shift?->clock_in_time) {
            return 0;
        }
        $expected = Carbon::parse($attendance->date->toDateString() . ' ' . $attendance->shift->clock_in_time)
            ->addMinutes((int) $attendance->shift->grace_in_minutes);
        return $attendance->clock_in_at->greaterThan($expected)
            ? $expected->diffInMinutes($attendance->clock_in_at)
            : 0;
    }
}
