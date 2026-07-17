<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRequest;
use App\Models\HrRequestPolicy;
use App\Services\HR\EmployeeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeRequestController extends Controller
{
    public function __construct(private EmployeeRequestService $service) {}

    public function index(Request $request): View
    {
        $companyId = (int) session('company_id');
        $types = array_values(array_filter((array) $request->route('request_types', [])));
        $query = EmployeeRequest::forCompany($companyId)
            ->with(['employee', 'branch', 'approver'])
            ->when($types, fn ($builder) => $builder->whereIn('type', $types))
            ->latest('submitted_at');

        return view('hr.requests.index', [
            'requests' => $query->paginate(20),
            'policies' => HrRequestPolicy::forCompany($companyId)->orderBy('name')->get(),
            'activeTypes' => $types,
        ]);
    }

    public function approve(Request $request, EmployeeRequest $employeeRequest): RedirectResponse
    {
        $this->ensureCompany($employeeRequest);
        $data = $request->validate([
            'approved_amount' => ['nullable', 'numeric', 'min:1'],
            'installment_count' => ['nullable', 'integer', 'min:1', 'max:60'],
            'hr_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->service->approve($employeeRequest, (int) $request->user()->id, $data);
        return back()->with('success', 'Pengajuan disetujui HR.');
    }

    public function reject(Request $request, EmployeeRequest $employeeRequest): RedirectResponse
    {
        $this->ensureCompany($employeeRequest);
        $data = $request->validate(['hr_note' => ['required', 'string', 'min:5', 'max:2000']]);
        $this->service->reject($employeeRequest, (int) $request->user()->id, $data['hr_note']);
        return back()->with('success', 'Pengajuan ditolak dengan catatan HR.');
    }

    public function updatePolicy(Request $request, HrRequestPolicy $policy): RedirectResponse
    {
        abort_if((int) $policy->company_id !== (int) session('company_id'), 403);
        $data = $request->validate([
            'max_amount' => ['nullable', 'numeric', 'min:0'],
            'max_days' => ['nullable', 'integer', 'min:1', 'max:366'],
            'max_installments' => ['nullable', 'integer', 'min:1', 'max:60'],
            'requires_document' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $policy->update([...$data, 'requires_document' => $request->boolean('requires_document'), 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Kebijakan pengajuan diperbarui.');
    }

    private function ensureCompany(EmployeeRequest $request): void
    {
        abort_if((int) $request->company_id !== (int) session('company_id'), 403);
    }
}
