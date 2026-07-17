<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use App\Models\BusinessTripPolicy;
use App\Models\Employee;
use App\Models\OperationalVehicle;
use App\Models\VehicleExpense;
use App\Services\Operations\BusinessTripService;
use App\Services\Operations\VehicleCostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function index(): View
    {
        $companyId=(int)session('company_id');
        return view('hr.operations.index',[
            'policy'=>BusinessTripPolicy::firstOrCreate(['company_id'=>$companyId],['daily_allowance'=>50000,'default_monthly_advance'=>1500000]),
            'trips'=>BusinessTrip::forCompany($companyId)->with('employee')->latest()->paginate(15,['*'],'trips'),
            'vehicles'=>OperationalVehicle::forCompany($companyId)->with('employee')->latest()->get(),
            'expenses'=>VehicleExpense::forCompany($companyId)->with('vehicle')->latest('planned_payment_date')->limit(30)->get(),
            'employees'=>Employee::forCompany($companyId)->active()->orderBy('name')->get(),
        ]);
    }

    public function updatePolicy(Request $request, BusinessTripPolicy $policy): RedirectResponse
    {
        abort_if((int)$policy->company_id!==(int)session('company_id'),403);
        $data=$request->validate(['daily_allowance'=>['required','numeric','min:0'],'default_monthly_advance'=>['required','numeric','min:0'],'hr_delegation_limit'=>['nullable','numeric','min:0'],'delegation_valid_until'=>['nullable','date'],'owner_approval_required'=>['nullable','boolean'],'transport_paid_by_company'=>['nullable','boolean']]);
        $policy->update([...$data,'owner_approval_required'=>$request->boolean('owner_approval_required'),'transport_paid_by_company'=>$request->boolean('transport_paid_by_company')]);
        return back()->with('success','Kebijakan perjalanan dinas diperbarui.');
    }

    public function storeTrip(Request $request, BusinessTripService $service): RedirectResponse
    {
        $companyId=(int)session('company_id');
        $data=$request->validate(['employee_id'=>['required',Rule::exists('employees','id')->where('company_id',$companyId)],'origin'=>['nullable','string','max:255'],'destination'=>['required','string','max:255'],'purpose'=>['required','string','min:5'],'start_date'=>['required','date'],'end_date'=>['required','date','after_or_equal:start_date'],'transport_budget'=>['nullable','numeric','min:0'],'lodging_budget'=>['nullable','numeric','min:0'],'other_budget'=>['nullable','numeric','min:0'],'advance_amount'=>['nullable','numeric','min:0']]);
        $service->submit($companyId,$data);
        return back()->with('success','Pengajuan perjalanan dinas dibuat.');
    }

    public function approveHr(Request $request, BusinessTrip $trip, BusinessTripService $service): RedirectResponse
    {
        $this->ensureTrip($trip); $service->approveByHr($trip,(int)$request->user()->id,$request->input('review_note')); return back()->with('success','Review HR tersimpan.');
    }

    public function approveOwner(Request $request, BusinessTrip $trip, BusinessTripService $service): RedirectResponse
    {
        $this->ensureTrip($trip); $service->approveByOwner($trip,(int)$request->user()->id,$request->input('review_note')); return back()->with('success','Perjalanan dinas disetujui owner.');
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $companyId=(int)session('company_id');
        $data=$request->validate(['code'=>['required','string','max:50',Rule::unique('operational_vehicles','code')->where('company_id',$companyId)],'employee_id'=>['nullable',Rule::exists('employees','id')->where('company_id',$companyId)],'ownership_type'=>['required',Rule::in(['company','employee_private'])],'plate_no'=>['nullable','string','max:30'],'monthly_operational_allowance'=>['required','numeric','min:0'],'monthly_fuel_budget'=>['required','numeric','min:0'],'service_interval_months'=>['required','integer','min:1','max:24'],'next_service_date'=>['nullable','date']]);
        $employee=isset($data['employee_id'])?Employee::forCompany($companyId)->find($data['employee_id']):null;
        OperationalVehicle::create([...$data,'company_id'=>$companyId,'branch_id'=>$employee?->branch_id,'is_active'=>true]);
        return back()->with('success','Motor operasional ditambahkan.');
    }

    public function planVehicleMonth(Request $request, VehicleCostService $service): RedirectResponse
    {
        $data=$request->validate(['period_year'=>['required','integer','between:2020,2100'],'period_month'=>['required','integer','between:1,12']]);
        $count=$service->planMonth((int)session('company_id'),(int)$data['period_year'],(int)$data['period_month']);
        return back()->with('success',"$count rencana biaya motor dibuat.");
    }

    private function ensureTrip(BusinessTrip $trip):void{abort_if((int)$trip->company_id!==(int)session('company_id'),403);}
}
