<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollSlip;
use Illuminate\View\View;

/** Company-scoped compliance overview; not an official BPJS/Coretax filing. */
class ComplianceReportController extends Controller
{
    public function index(): View
    {
        $companyId = (int) session('company_id');
        $employees = Employee::forCompany($companyId)->active();
        $latestPeriod = PayrollPeriod::forCompany($companyId)->where('status', 'published')
            ->latest('period_year')->latest('period_month')->first();
        $slips = $latestPeriod
            ? PayrollSlip::forCompany($companyId)->where('payroll_period_id', $latestPeriod->id)
            : PayrollSlip::forCompany($companyId)->whereRaw('1 = 0');

        return view('hr.reports.compliance.index', [
            'latestPeriod' => $latestPeriod,
            'summary' => [
                'employees' => (clone $employees)->count(),
                'health_active' => (clone $employees)->where('is_bpjs_kesehatan_active', true)->count(),
                'employment_active' => (clone $employees)->where('is_bpjs_ketenagakerjaan_active', true)->count(),
                'coretax_ready' => (clone $employees)->whereNotNull('identity_number')->whereNotNull('npwp')->whereNotNull('tax_status')->count(),
                'bpjs_company' => (float) (clone $slips)->sum('bpjs_kesehatan_perusahaan') + (float) (clone $slips)->sum('jht_perusahaan') + (float) (clone $slips)->sum('jp_perusahaan') + (float) (clone $slips)->sum('jkk') + (float) (clone $slips)->sum('jkm'),
                'bpjs_employee' => (float) (clone $slips)->sum('bpjs_kesehatan_karyawan') + (float) (clone $slips)->sum('jht_karyawan') + (float) (clone $slips)->sum('jp_karyawan'),
            ],
            'employeesNeedingData' => $employees->where(fn ($query) => $query->whereNull('identity_number')->orWhereNull('tax_status'))->orderBy('name')->limit(25)->get(),
        ]);
    }
}
