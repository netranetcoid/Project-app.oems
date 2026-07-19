<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\BpjsSetting;
use App\Services\Payroll\PayrollCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Pengaturan dan simulasi BPJS; perhitungan dipusatkan pada service payroll. */
class BpjsCalculationController extends Controller
{
    public function __construct(private PayrollCalculationService $calculator)
    {
    }

    public function index(): View
    {
        $companyId = (int) session('company_id');

        return view('setting.bpjs-calculation.index', [
            'setting' => $this->calculator->settingForCompany($companyId),
            'riskOptions' => BpjsSetting::riskOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = $this->calculator->settingForCompany((int) session('company_id'));
        $data = $this->validatedSettings($request);
        $data['aktif'] = $request->boolean('aktif');
        $setting->update($data);

        return back()->with('success', 'Konfigurasi BPJS tersimpan. Payroll baru akan memakai tarif ini; slip historis tetap memakai snapshot lama.');
    }

    public function preview(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'fixed_allowance' => ['nullable', 'numeric', 'min:0'],
            'bpjs_kesehatan_active' => ['nullable', 'boolean'],
            'bpjs_ketenagakerjaan_active' => ['nullable', 'boolean'],
            'risk_code' => ['required', Rule::in(array_keys(BpjsSetting::riskOptions()))],
        ]);

        $calculation = $this->calculator->calculate(
            basicSalary: (float) $data['basic_salary'],
            fixedAllowance: (float) ($data['fixed_allowance'] ?? 0),
            bpjsKesehatanActive: $request->boolean('bpjs_kesehatan_active'),
            bpjsKetenagakerjaanActive: $request->boolean('bpjs_ketenagakerjaan_active'),
            riskCode: $data['risk_code'],
            setting: $this->calculator->settingForCompany((int) session('company_id')),
        );
        $summary = $this->calculator->finalizePayroll(
            (float) $data['basic_salary'] + (float) ($data['fixed_allowance'] ?? 0),
            0,
            $calculation,
        );

        return back()->withInput()->with('bpjs_preview', [...$calculation, ...$summary]);
    }

    private function validatedSettings(Request $request): array
    {
        return $request->validate([
            'bpjs_kesehatan_perusahaan' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_kesehatan_karyawan' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_kesehatan_wage_cap' => ['nullable', 'numeric', 'min:0'],
            'jht_perusahaan' => ['required', 'numeric', 'min:0', 'max:100'],
            'jht_karyawan' => ['required', 'numeric', 'min:0', 'max:100'],
            'jp_perusahaan' => ['required', 'numeric', 'min:0', 'max:100'],
            'jp_karyawan' => ['required', 'numeric', 'min:0', 'max:100'],
            'batas_upah_jp' => ['nullable', 'numeric', 'min:0'],
            'jkm' => ['required', 'numeric', 'min:0', 'max:100'],
            'jkk_sangat_rendah' => ['required', 'numeric', 'min:0', 'max:100'],
            'jkk_rendah' => ['required', 'numeric', 'min:0', 'max:100'],
            'jkk_sedang' => ['required', 'numeric', 'min:0', 'max:100'],
            'jkk_tinggi' => ['required', 'numeric', 'min:0', 'max:100'],
            'jkk_sangat_tinggi' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_jkk_risk_code' => ['required', Rule::in(array_keys(BpjsSetting::riskOptions()))],
            'effective_from' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
