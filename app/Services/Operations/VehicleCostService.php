<?php

namespace App\Services\Operations;

use App\Models\OperationalVehicle;
use App\Models\VehicleExpense;
use Carbon\Carbon;

class VehicleCostService
{
    public function planMonth(int $companyId, int $year, int $month): int
    {
        $period=Carbon::create($year,$month,1);
        $created=0;
        OperationalVehicle::forCompany($companyId)->where('is_active',true)->chunkById(100,function($vehicles) use($companyId,$period,&$created){
            foreach($vehicles as $vehicle){
                $plans=[
                    'operational'=>[(float)$vehicle->monthly_operational_allowance,$period->copy()->startOfMonth()],
                    'fuel'=>[(float)$vehicle->monthly_fuel_budget,$period->copy()->startOfMonth()],
                ];
                if($vehicle->next_service_date && $vehicle->next_service_date->between($period->copy()->startOfMonth(),$period->copy()->endOfMonth())){
                    // Service aktual dibayar pada awal bulan berikutnya sesuai rule owner.
                    $plans['service']=[0.0,$period->copy()->addMonthNoOverflow()->startOfMonth()];
                }
                foreach($plans as $type=>[$amount,$payDate]){
                    if($amount<=0 && $type!=='service') continue;
                    $expense=VehicleExpense::firstOrCreate(
                        ['operational_vehicle_id'=>$vehicle->id,'period_year'=>$period->year,'period_month'=>$period->month,'type'=>$type],
                        ['company_id'=>$companyId,'employee_id'=>$vehicle->employee_id,'planned_amount'=>$amount,'planned_payment_date'=>$payDate->toDateString(),'status'=>'planned']
                    );
                    if($expense->wasRecentlyCreated) $created++;
                }
            }
        });
        return $created;
    }
}
