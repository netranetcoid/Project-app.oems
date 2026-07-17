<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Models\PayrollSlip;
use App\Services\Payroll\PayrollService;
use App\Services\Integration\AppBillIntegrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(
        private PayrollService $service,
        private AppBillIntegrationService $appBill
    ) {}

    public function index(): View
    {
        return view('hr.payroll.index', [
            'periods' => PayrollPeriod::forCompany((int) session('company_id'))->withCount('slips')->latest('period_year')->latest('period_month')->paginate(18),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate(['period_year' => ['required', 'integer', 'min:2020', 'max:2100'], 'period_month' => ['required', 'integer', 'between:1,12']]);
        $period = $this->service->generate((int) session('company_id'), (int) $data['period_year'], (int) $data['period_month'], (int) $request->user()->id);
        return redirect()->route('hr.payroll.show', $period)->with('success', 'Draft payroll berhasil dihitung.');
    }

    public function show(PayrollPeriod $period): View
    {
        $this->ensureCompany($period);
        return view('hr.payroll.show', ['period' => $period, 'slips' => $period->slips()->with(['employee', 'branch'])->paginate(30)]);
    }

    public function approve(Request $request, PayrollPeriod $period): RedirectResponse
    {
        $this->ensureCompany($period);
        $this->service->approve($period, (int) $request->user()->id);
        return back()->with('success', 'Payroll disetujui HR.');
    }

    public function publish(Request $request, PayrollPeriod $period): RedirectResponse
    {
        $this->ensureCompany($period);
        $period = $this->service->publish($period, (int) $request->user()->id);
        $event = $this->appBill->queuePayrollPeriod($period);
        $message = 'Slip payroll diterbitkan ke OvallHR.';
        if ($event) {
            $message .= ' Payload detail terenkripsi masuk antrean AppBill dummy.';
        }
        return back()->with('success', $message);
    }

    public function payslip(PayrollSlip $slip): View
    {
        abort_if((int) $slip->company_id !== (int) session('company_id'), 403);
        return view('hr.payroll.payslip', ['slip' => $slip->load(['period', 'items'])]);
    }

    private function ensureCompany(PayrollPeriod $period): void
    {
        abort_if((int) $period->company_id !== (int) session('company_id'), 403);
    }
}
