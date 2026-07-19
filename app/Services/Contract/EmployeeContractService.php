<?php

namespace App\Services\Contract;

use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\ContractType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeContractService
{
    /*
    |--------------------------------------------------------------------------
    | CREATE CONTRACT
    |--------------------------------------------------------------------------
    */

    public function store(array $data): EmployeeContract
    {
        return DB::transaction(function () use ($data) {

            $companyId = (int) session('company_id');
            if (!$companyId) {
                throw new \RuntimeException('Company aktif belum dipilih.');
            }

            $employee = Employee::with([
                'branch',
                'division',
                'position',
            ])->forCompany($companyId)->findOrFail($data['employee_id']);

            $type = ContractType::query()
                ->forCompany($companyId)
                ->active()
                ->findOrFail($data['contract_type_id']);

            $this->applyTermDefaults($data, $type);

            // Kontrak lama bukan latest lagi
            EmployeeContract::where('company_id', $employee->company_id)
                ->where('employee_id', $employee->id)
                ->update([
                    'is_latest' => false,
                ]);

            // Nomor Kontrak
            $data['contract_no'] = $this->generateContractNumber();

            // Sequence
            $data['contract_sequence'] =
                EmployeeContract::where('company_id', $employee->company_id)
                    ->where('employee_id', $employee->id)
                    ->count() + 1;

            $data['contract_version'] = 1;

            // Snapshot Pegawai
            $this->fillEmployeeSnapshot($employee, $data);

            // Snapshot Payroll
            $this->fillPayrollSnapshot($employee, $data);

            $data['company_id'] = $employee->company_id;

            $data['created_by'] = Auth::id();

            $data['is_latest'] = true;

            $data['status'] = $data['status'] ?? 'draft';
            // Employee contracts are never edited as a document. They receive
            // an immutable snapshot from Contract Master at creation time.
            $data['contract_body'] = $type->template_body;
            $data['settings'] = array_merge(
                is_array($data['settings'] ?? null) ? $data['settings'] : [],
                ['template_key' => $type->template_key, 'template_version' => $type->template_version]
            );

            return EmployeeContract::create($data);

        });
    }

    /*
    |--------------------------------------------------------------------------
    | EXTEND CONTRACT
    |--------------------------------------------------------------------------
    */

    public function extend(
        EmployeeContract $contract,
        array $data
    ): EmployeeContract {

        return DB::transaction(function () use ($contract, $data) {

            $companyId = (int) session('company_id');
            if (!$companyId || (int) $contract->company_id !== $companyId) {
                abort(403, 'Kontrak bukan bagian dari company aktif.');
            }

            $contract->update([
                'is_latest' => false,
                'status'    => 'extended',
            ]);

            $employee = Employee::with([
                'branch',
                'division',
                'position',
            ])->forCompany($companyId)->findOrFail($contract->employee_id);

            $type = ContractType::query()
                ->forCompany($employee->company_id)
                ->active()
                ->findOrFail($data['contract_type_id']);

            $this->applyTermDefaults($data, $type);

            $data['company_id'] = $employee->company_id;

            $data['employee_id'] = $employee->id;

            $data['parent_contract_id'] = $contract->id;

            $data['contract_no'] = $this->generateContractNumber();

            $data['contract_sequence'] =
                $contract->contract_sequence + 1;

            $data['contract_version'] = 1;

            $this->fillEmployeeSnapshot($employee, $data);

            $this->fillPayrollSnapshot($employee, $data);

            $data['created_by'] = Auth::id();

            $data['is_latest'] = true;

            $data['status'] = $data['status'] ?? 'draft';
            // An extension is a new document and therefore takes the current
            // approved Contract Master snapshot.
            $data['contract_body'] = $type->template_body;
            $data['settings'] = [
                'template_key' => $type->template_key,
                'template_version' => $type->template_version,
            ];

            return EmployeeContract::create($data);

        });
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        EmployeeContract $contract,
        array $data
    ): EmployeeContract {

        $type = ContractType::query()
            ->forCompany($contract->company_id)
            ->active()
            ->findOrFail($data['contract_type_id']);

        $this->applyTermDefaults($data, $type);
        // Editing an employee contract may change dates or administrative
        // fields only. Its document body stays frozen unless HR intentionally
        // changes the master type, in which case a new master snapshot is used.
        $data['contract_body'] = (int) $contract->contract_type_id === (int) $type->id
            ? ($contract->contract_body ?: $type->template_body)
            : $type->template_body;
        $data['settings'] = array_merge(
            is_array($contract->settings) ? $contract->settings : [],
            ['template_key' => $type->template_key, 'template_version' => $type->template_version]
        );

        $contract->update($data);

        return $contract->fresh();

    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function delete(EmployeeContract $contract): void
{
    DB::transaction(function () use ($contract) {

        $employeeId = $contract->employee_id;
        $latest     = $contract->is_latest;

        $contract->delete();

        if ($latest) {

            $previous = EmployeeContract::where('employee_id', $employeeId)
                ->latest('start_date')
                ->first();

            if ($previous) {
                $previous->update([
                    'is_latest' => true,
                ]);
            }
        }
    });
}

    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */

   public function approve(
    EmployeeContract $contract
): EmployeeContract {

    if ((int) $contract->company_id !== (int) session('company_id')) {
        abort(403, 'Kontrak bukan bagian dari company aktif.');
    }

    DB::transaction(function () use ($contract) {

        $contract->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $employmentStatus = match ($contract->contractType?->template_key) {
            'internship' => 'internship',
            'probation' => 'probation',
            default => 'contract',
        };

        Employee::where('id', $contract->employee_id)
            ->update([
                'employment_status'  => $employmentStatus,
                'contract_start_date'=> $contract->start_date,
                'contract_end_date'  => $contract->end_date,
                'probation_end_date' => $contract->probation_end_date,
            ]);

    });

    return $contract->fresh();

}

    /*
    |--------------------------------------------------------------------------
    | GENERATE CONTRACT NUMBER
    |--------------------------------------------------------------------------
    */

   public function generateContractNumber(): string
{
    $year = now()->year;

    $lastId = EmployeeContract::max('id') ?? 0;

    return sprintf(
        'CTR/%04d/%06d',
        $year,
        $lastId + 1
    );
}
    /*
    |--------------------------------------------------------------------------
    | Snapshot Pegawai
    |--------------------------------------------------------------------------
    */

    protected function fillEmployeeSnapshot(
        Employee $employee,
        array &$data
    ): void {

        $data['employee_no'] = $employee->employee_no;

        $data['employee_name'] = $employee->name;

        $data['email'] = $employee->email;

        $data['phone'] = $employee->phone;

        $data['branch_name'] = $employee->branch?->name;

        $data['division_name'] = $employee->division?->name;

        $data['position_name'] = $employee->position?->name;

    }

    /*
    |--------------------------------------------------------------------------
    | Snapshot Payroll
    |--------------------------------------------------------------------------
    */

    protected function fillPayrollSnapshot(
        Employee $employee,
        array &$data
    ): void {

        $data['basic_salary'] = $employee->basic_salary;

        $data['meal_allowance'] = $employee->meal_allowance;

        $data['transport_allowance'] = $employee->transport_allowance;

        $data['position_allowance'] = $employee->position_allowance;

        $data['fixed_allowance'] = $employee->fixed_allowance;

    }

    protected function applyTermDefaults(array &$data, ContractType $type): void
    {
        $duration = isset($data['duration_month']) && $data['duration_month'] !== ''
            ? (int) $data['duration_month']
            : (int) ($type->default_duration_month ?? 0);

        if ($duration > 0) {
            $data['duration_month'] = $duration;
        }

        if ($type->is_probation) {
            $data['end_date'] = null;
            if ($duration > 0 && !empty($data['start_date'])) {
                $data['probation_end_date'] = Carbon::parse($data['start_date'])
                    ->addMonthsNoOverflow($duration)
                    ->subDay()
                    ->toDateString();
            }
            return;
        }

        if (empty($data['end_date']) && $duration > 0 && !empty($data['start_date'])) {
            $data['end_date'] = Carbon::parse($data['start_date'])
                ->addMonthsNoOverflow($duration)
                ->subDay()
                ->toDateString();
        }
    }
}
