<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kpi\StoreEmployeeKpiAssessmentRequest;
use App\Http\Requests\Kpi\StoreKpiAspectRequest;
use App\Http\Requests\Kpi\StoreKpiStandardRequest;
use App\Models\Employee;
use App\Models\EmployeeKpiAssessment;
use App\Models\KpiAspect;
use App\Models\KpiStandard;
use App\Models\Position;
use App\Services\Kpi\KpiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KpiController extends Controller
{
    public function __construct(protected KpiService $service) {}

    public function index(): View
    {
        $companyId = (int) session('company_id');
        $assessments = EmployeeKpiAssessment::forCompany($companyId)
            ->with(['employee', 'position', 'assessor', 'approver'])
            ->latest('period_year')
            ->latest('period_month')
            ->paginate(15);

        return view('hr.kpi.index', [
            'assessments' => $assessments,
            'stats' => [
                'aspects' => KpiAspect::forCompany($companyId)->active()->count(),
                'standards' => KpiStandard::forCompany($companyId)->active()->count(),
                'submitted' => EmployeeKpiAssessment::forCompany($companyId)->where('status', 'submitted')->count(),
                'approved' => EmployeeKpiAssessment::forCompany($companyId)->where('status', 'approved')->count(),
            ],
        ]);
    }

    public function aspects(): View
    {
        return view('hr.kpi.aspects', [
            'aspects' => KpiAspect::forCompany((int) session('company_id'))->latest()->paginate(20),
        ]);
    }

    public function storeAspect(StoreKpiAspectRequest $request): RedirectResponse
    {
        $this->service->createAspect($request->validated());

        return redirect()->route('hr.kpi.aspects')->with('success', 'Aspek KPI berhasil ditambahkan.');
    }

    public function createStandard(): View
    {
        $companyId = (int) session('company_id');

        return view('hr.kpi.standard-create', [
            'positions' => Position::forCompany($companyId)->active()->orderBy('name')->get(),
            'aspects' => KpiAspect::forCompany($companyId)->active()->orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function storeStandard(StoreKpiStandardRequest $request): RedirectResponse
    {
        $this->service->createStandard($request->validated());

        return redirect()->route('hr.kpi.index')->with('success', 'Standar KPI jabatan berhasil disimpan.');
    }

    public function createAssessment(): View
    {
        $companyId = (int) session('company_id');

        return view('hr.kpi.assessment-create', [
            'employees' => Employee::forCompany($companyId)->active()->with('position')->orderBy('name')->get(),
            'standards' => KpiStandard::forCompany($companyId)->active()->with(['position', 'items'])->orderBy('name')->get(),
        ]);
    }

    public function storeAssessment(StoreEmployeeKpiAssessmentRequest $request): RedirectResponse
    {
        $assessment = $this->service->createAssessment($request->validated(), (int) $request->user()->id);

        return redirect()->route('hr.kpi.assessments.show', $assessment)->with('success', 'KPI berhasil diajukan untuk approval.');
    }

    public function show(EmployeeKpiAssessment $assessment): View
    {
        $this->ensureCompany($assessment);
        $assessment->load(['employee', 'position', 'standard', 'assessor', 'approver', 'items']);

        return view('hr.kpi.show', compact('assessment'));
    }

    public function approve(Request $request, EmployeeKpiAssessment $assessment): RedirectResponse
    {
        $this->ensureCompany($assessment);
        $this->service->approve($assessment, (int) $request->user()->id, $request->string('review_note')->toString() ?: null);

        return back()->with('success', 'KPI telah disetujui dan bonus siap diproses payroll.');
    }

    public function reject(Request $request, EmployeeKpiAssessment $assessment): RedirectResponse
    {
        $this->ensureCompany($assessment);
        $validated = $request->validate(['review_note' => ['required', 'string', 'max:1000']]);
        $this->service->reject($assessment, (int) $request->user()->id, $validated['review_note']);

        return back()->with('success', 'KPI dikembalikan untuk diperbaiki.');
    }

    private function ensureCompany(EmployeeKpiAssessment $assessment): void
    {
        abort_if((int) $assessment->company_id !== (int) session('company_id'), 403);
    }
}
