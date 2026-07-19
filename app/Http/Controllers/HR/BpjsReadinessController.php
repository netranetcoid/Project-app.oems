<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\BpjsReadinessProfile;
use App\Models\Company;
use App\Models\Employee;
use App\Models\BpjsSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pusat kesiapan BPJS: mengumpulkan data F1/F1a/F2, bukan integrasi atau
 * pengajuan otomatis ke BPJS. Pengiriman tetap dilakukan HR di kanal resmi.
 */
class BpjsReadinessController extends Controller
{
    public function index(): View
    {
        $companyId = (int) session('company_id');
        $company = Company::findOrFail($companyId);
        $profile = BpjsReadinessProfile::firstOrCreate(['company_id' => $companyId]);
        $employees = Employee::query()->forCompany($companyId)->active()
            ->with('documents')
            ->orderBy('name')
            ->get();

        $readyEmployees = $employees->filter(fn (Employee $employee) =>
            filled($employee->identity_number)
            && filled($employee->kk_number)
            && filled($employee->join_date)
            && $this->hasVerifiedDocument($employee, 'ktp')
            && $this->hasVerifiedDocument($employee, 'kk')
        );

        return view('hr.bpjs-readiness.index', compact('company', 'profile', 'employees', 'readyEmployees'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'registration_status' => ['required', 'in:draft,preparing,submitted,active,needs_review'],
            'bpjs_ketenagakerjaan_npp' => ['nullable', 'string', 'max:100'],
            'bpjs_kesehatan_registration_no' => ['nullable', 'string', 'max:100'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_position' => ['nullable', 'string', 'max:255'],
            'pic_email' => ['nullable', 'email', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'target_registration_date' => ['nullable', 'date'],
            'submitted_at' => ['nullable', 'date'],
            'activated_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'check_nib_npwp' => ['nullable', 'boolean'],
            'check_company_identity' => ['nullable', 'boolean'],
            'check_worker_identity' => ['nullable', 'boolean'],
            'check_worker_wage' => ['nullable', 'boolean'],
            'check_f1_f1a_f2' => ['nullable', 'boolean'],
        ]);

        $data['document_checklist'] = collect([
            'nib_npwp' => $request->boolean('check_nib_npwp'),
            'company_identity' => $request->boolean('check_company_identity'),
            'worker_identity' => $request->boolean('check_worker_identity'),
            'worker_wage' => $request->boolean('check_worker_wage'),
            'f1_f1a_f2' => $request->boolean('check_f1_f1a_f2'),
        ])->all();
        unset($data['check_nib_npwp'], $data['check_company_identity'], $data['check_worker_identity'], $data['check_worker_wage'], $data['check_f1_f1a_f2']);

        BpjsReadinessProfile::updateOrCreate(['company_id' => session('company_id')], $data);

        return back()->with('success', 'Kesiapan pendaftaran BPJS PT OSM berhasil disimpan.');
    }

    public function updateEmployee(Request $request, Employee $employee): RedirectResponse
    {
        abort_if($employee->company_id !== (int) session('company_id'), 403);

        $data = $request->validate([
            'bpjs_registration_status' => ['required', 'in:pending,ready,submitted,active,needs_review,not_applicable'],
            'bpjs_kesehatan_no' => ['nullable', 'string', 'max:100'],
            'bpjs_ketenagakerjaan_no' => ['nullable', 'string', 'max:100'],
            'bpjs_effective_date' => ['nullable', 'date'],
            'bpjs_notes' => ['nullable', 'string', 'max:1000'],
            // Kategori dipakai PayrollCalculationService untuk memilih tarif JKK
            // yang telah HR konfigurasi, bukan untuk menyimpan persentase pegawai.
            'bpjs_jkk_risk_code' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(BpjsSetting::riskOptions()))],
        ]);

        $data['is_bpjs_kesehatan_active'] = $data['bpjs_registration_status'] === 'active' && filled($data['bpjs_kesehatan_no']);
        $data['is_bpjs_ketenagakerjaan_active'] = $data['bpjs_registration_status'] === 'active' && filled($data['bpjs_ketenagakerjaan_no']);
        $employee->update($data);

        return back()->with('success', "Status BPJS {$employee->name} berhasil diperbarui.");
    }

    public function print(): View
    {
        $companyId = (int) session('company_id');

        return view('hr.bpjs-readiness.print', [
            'company' => Company::findOrFail($companyId),
            'profile' => BpjsReadinessProfile::firstOrCreate(['company_id' => $companyId]),
            'employees' => Employee::query()->forCompany($companyId)->active()->with('documents')->orderBy('name')->get(),
        ]);
    }

    /** BPJS readiness requires both data fields and verified supporting files. */
    private function hasVerifiedDocument(Employee $employee, string $type): bool
    {
        return $employee->documents->contains(fn ($document) =>
            $document->document_type === $type && $document->status === 'verified'
        );
    }
}
