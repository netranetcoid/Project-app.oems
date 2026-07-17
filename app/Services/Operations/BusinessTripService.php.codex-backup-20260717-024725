<?php

namespace App\Services\Operations;

use App\Models\BusinessTrip;
use App\Models\BusinessTripPolicy;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BusinessTripService
{
    public function submit(int $companyId, array $data): BusinessTrip
    {
        $employee = Employee::forCompany($companyId)->findOrFail($data['employee_id']);
        $policy = BusinessTripPolicy::query()->where('company_id',$companyId)->firstOrFail();
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $days = $start->diffInDays($end) + 1;
        $daily = (float)($data['daily_allowance'] ?? $policy->daily_allowance);
        $budget = ($daily * $days) + (float)($data['transport_budget'] ?? 0) + (float)($data['lodging_budget'] ?? 0) + (float)($data['other_budget'] ?? 0);

        return BusinessTrip::create([
            'company_id'=>$companyId,'branch_id'=>$employee->branch_id,'employee_id'=>$employee->id,
            'trip_no'=>'DINAS-'.now()->format('Ymd').'-'.Str::upper(Str::random(7)),
            'origin'=>$data['origin'] ?? null,'destination'=>$data['destination'],'purpose'=>$data['purpose'],
            'start_date'=>$start->toDateString(),'end_date'=>$end->toDateString(),'total_days'=>$days,
            'daily_allowance'=>$daily,'transport_budget'=>$data['transport_budget'] ?? 0,
            'lodging_budget'=>$data['lodging_budget'] ?? 0,'other_budget'=>$data['other_budget'] ?? 0,
            'advance_amount'=>$data['advance_amount'] ?? $budget,'status'=>'submitted','policy_snapshot'=>$policy->toArray(),
        ]);
    }

    public function approveByHr(BusinessTrip $trip, int $userId, ?string $note = null): BusinessTrip
    {
        return DB::transaction(function() use($trip,$userId,$note){
            $trip=BusinessTrip::query()->lockForUpdate()->findOrFail($trip->id);
            if($trip->status!=='submitted') throw ValidationException::withMessages(['status'=>'Dinas sudah diproses.']);
            $policy=BusinessTripPolicy::query()->where('company_id',$trip->company_id)->firstOrFail();
            $delegationActive=$policy->hr_delegation_limit && (!$policy->delegation_valid_until || $policy->delegation_valid_until->endOfDay()->isFuture()) && (float)$trip->advance_amount <= (float)$policy->hr_delegation_limit;
            $status=(!$policy->owner_approval_required || $delegationActive) ? 'approved' : 'hr_approved';
            $trip->update(['status'=>$status,'hr_approved_by'=>$userId,'hr_approved_at'=>now(),'delegation_used'=>$delegationActive,'review_note'=>$note]);
            return $trip->fresh();
        });
    }

    public function approveByOwner(BusinessTrip $trip, int $userId, ?string $note = null): BusinessTrip
    {
        if($trip->status!=='hr_approved') throw ValidationException::withMessages(['status'=>'Dinas harus lolos review HR terlebih dahulu.']);
        $trip->update(['status'=>'approved','owner_approved_by'=>$userId,'owner_approved_at'=>now(),'review_note'=>$note ?: $trip->review_note]);
        return $trip->fresh();
    }

    public function settle(BusinessTrip $trip, array $items): BusinessTrip
    {
        if(!in_array($trip->status,['approved','in_progress','returned'],true)) throw ValidationException::withMessages(['status'=>'Dinas belum dapat diselesaikan.']);
        $actual=collect($items)->sum(fn($item)=>(float)($item['amount']??0));
        $trip->update(['status'=>'settled','actual_amount'=>$actual,'settlement_difference'=>(float)$trip->advance_amount-$actual,'settlement_items'=>$items,'returned_at'=>$trip->returned_at ?: now(),'settled_at'=>now()]);
        return $trip->fresh();
    }
}
