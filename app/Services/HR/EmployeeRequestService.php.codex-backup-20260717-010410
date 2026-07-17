<?php

namespace App\Services\HR;

use App\Models\Employee;
use App\Models\EmployeeReceivable;
use App\Models\EmployeeRequest;
use App\Models\HrRequestPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeRequestService
{
    public const TYPES = ['leave', 'permission', 'sick', 'overtime', 'reimbursement', 'cash_advance', 'receivable'];

    public function submit(Employee $employee, array $data, ?string $documentPath = null): EmployeeRequest
    {
        $type = (string) $data['type'];
        $policy = HrRequestPolicy::forCompany((int) $employee->company_id)
            ->where('type', $type)->where('is_active', true)->first();

        $start = isset($data['start_date']) ? Carbon::parse($data['start_date'])->startOfDay() : null;
        $end = isset($data['end_date']) ? Carbon::parse($data['end_date'])->startOfDay() : null;
        $days = ($start && $end) ? $start->diffInDays($end) + 1 : null;
        $amount = isset($data['requested_amount']) ? (float) $data['requested_amount'] : null;
        $installments = isset($data['installment_count']) ? (int) $data['installment_count'] : null;

        // Validasi kebijakan dilakukan lagi di service agar tidak dapat dilewati
        // oleh klien API lain selain aplikasi OvallHR.
        if ($policy?->requires_document && ! $documentPath) {
            throw ValidationException::withMessages(['document' => 'Dokumen pendukung wajib diunggah.']);
        }
        if ($policy?->max_days && $days && $days > $policy->max_days) {
            throw ValidationException::withMessages(['end_date' => "Maksimal {$policy->max_days} hari."]);
        }
        if ($policy?->max_amount && $amount && $amount > (float) $policy->max_amount) {
            throw ValidationException::withMessages(['requested_amount' => 'Nominal melebihi batas kebijakan HR.']);
        }
        if ($policy?->max_installments && $installments && $installments > $policy->max_installments) {
            throw ValidationException::withMessages(['installment_count' => 'Jumlah cicilan melebihi batas kebijakan HR.']);
        }

        return EmployeeRequest::create([
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'employee_id' => $employee->id,
            'request_no' => 'REQ-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8)),
            'type' => $type,
            'start_date' => $start?->toDateString(),
            'end_date' => $end?->toDateString(),
            'total_days' => $days,
            'requested_amount' => $amount,
            'installment_count' => $installments,
            'reason' => trim((string) $data['reason']),
            'document_path' => $documentPath,
            'status' => 'submitted',
            'submitted_at' => now(),
            'metadata' => ['policy_snapshot' => $policy?->toArray()],
        ]);
    }

    public function approve(EmployeeRequest $request, int $approverId, array $data = []): EmployeeRequest
    {
        return DB::transaction(function () use ($request, $approverId, $data): EmployeeRequest {
            $request = EmployeeRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($request->status !== 'submitted') {
                throw ValidationException::withMessages(['status' => 'Pengajuan ini sudah diproses.']);
            }

            $approvedAmount = array_key_exists('approved_amount', $data)
                ? (float) $data['approved_amount'] : (float) ($request->requested_amount ?? 0);
            $installments = max(1, (int) ($data['installment_count'] ?? $request->installment_count ?? 1));

            $request->update([
                'approved_amount' => $approvedAmount ?: null,
                'installment_count' => $request->requested_amount ? $installments : $request->installment_count,
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
                'hr_note' => $data['hr_note'] ?? null,
            ]);

            if (in_array($request->type, ['cash_advance', 'receivable'], true) && $approvedAmount > 0) {
                EmployeeReceivable::firstOrCreate(
                    ['source_request_id' => $request->id],
                    [
                        'company_id' => $request->company_id,
                        'branch_id' => $request->branch_id,
                        'employee_id' => $request->employee_id,
                        'receivable_no' => 'PIU-' . now()->format('Ymd') . '-' . Str::upper(Str::random(7)),
                        'type' => $request->type,
                        'principal_amount' => $approvedAmount,
                        'remaining_amount' => $approvedAmount,
                        'installment_amount' => round($approvedAmount / $installments, 2),
                        'installment_count' => $installments,
                        'first_deduction_date' => now()->addMonthNoOverflow()->endOfMonth()->toDateString(),
                        'status' => 'active',
                        'approved_by' => $approverId,
                        'approved_at' => now(),
                        'notes' => $data['hr_note'] ?? null,
                    ]
                );
            }

            return $request->fresh(['employee', 'approver']);
        });
    }

    public function reject(EmployeeRequest $request, int $approverId, string $note): EmployeeRequest
    {
        if ($request->status !== 'submitted') {
            throw ValidationException::withMessages(['status' => 'Pengajuan ini sudah diproses.']);
        }
        $request->update([
            'status' => 'rejected', 'rejected_by' => $approverId,
            'rejected_at' => now(), 'hr_note' => $note,
        ]);
        return $request->fresh(['employee', 'rejector']);
    }
}
