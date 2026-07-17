<?php

namespace Database\Seeders;

use App\Models\BusinessTripPolicy;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BusinessTripPolicySeeder extends Seeder
{
    public function run(): void
    {
        foreach (Company::query()->get() as $company) {
            BusinessTripPolicy::firstOrCreate(['company_id'=>$company->id], [
                'daily_allowance'=>50000,
                'default_monthly_advance'=>1500000,
                'transport_paid_by_company'=>true,
                'owner_approval_required'=>true,
                'proof_retention_days'=>60,
                'settings'=>['editable'=>true,'advance_paid_before_departure'=>true,'settlement_required'=>true],
            ]);
        }
    }
}
