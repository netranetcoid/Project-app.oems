<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\HrRequestPolicy;
use Illuminate\Database\Seeder;

class HrRequestPolicySeeder extends Seeder
{
    public function run(): void
    {
        foreach (Company::query()->get() as $company) {
            $policies = [
                ['type' => 'leave', 'name' => 'Cuti', 'settings' => ['operational_availability_required' => true]],
                ['type' => 'permission', 'name' => 'Izin', 'settings' => ['operational_availability_required' => true]],
                ['type' => 'sick', 'name' => 'Sakit', 'requires_document' => true],
                ['type' => 'overtime', 'name' => 'Lembur'],
                ['type' => 'reimbursement', 'name' => 'Klaim Biaya', 'requires_document' => true],
                ['type' => 'cash_advance', 'name' => 'Kasbon', 'max_installments' => 1, 'settings' => ['suggested_max_salary_percent' => 30]],
                ['type' => 'receivable', 'name' => 'Piutang Karyawan', 'max_installments' => 12, 'settings' => ['suggested_max_salary_percent' => 100]],
            ];
            foreach ($policies as $policy) {
                HrRequestPolicy::updateOrCreate(
                    ['company_id' => $company->id, 'type' => $policy['type']],
                    [...$policy, 'company_id' => $company->id, 'is_active' => true]
                );
            }
        }
    }
}
