<?php

namespace App\Services\Payroll;

use App\Models\BpjsSetting;
use App\Models\Employee;
use Illuminate\Validation\ValidationException;

/**
 * Single source of truth untuk hitung BPJS dan total payroll.
 * Tarif maupun batas upah tidak diletakkan di controller/model/kode rumus.
 */
class PayrollCalculationService
{
    public function settingForCompany(int $companyId): BpjsSetting
    {
        $setting = BpjsSetting::query()->forCompany($companyId)->active()->first();

        if (! $setting) {
            throw ValidationException::withMessages([
                'bpjs' => 'Konfigurasi BPJS aktif belum tersedia. Lengkapi BPJS Calculation Engine terlebih dahulu.',
            ]);
        }

        return $setting;
    }

    public function calculateForEmployee(Employee $employee, BpjsSetting $setting): array
    {
        return $this->calculate(
            basicSalary: (float) $employee->basic_salary,
            fixedAllowance: (float) $employee->fixed_allowance,
            bpjsKesehatanActive: (bool) $employee->is_bpjs_kesehatan_active,
            bpjsKetenagakerjaanActive: (bool) $employee->is_bpjs_ketenagakerjaan_active,
            riskCode: $employee->bpjs_jkk_risk_code ?: $setting->default_jkk_risk_code,
            setting: $setting,
        );
    }

    /**
     * Dipakai oleh kalkulator pratinjau dan payroll. Argumen ini sengaja
     * eksplisit agar hanya empat input utama yang diperlukan HR.
     */
    public function calculate(
        float $basicSalary,
        float $fixedAllowance,
        bool $bpjsKesehatanActive,
        bool $bpjsKetenagakerjaanActive,
        string $riskCode,
        BpjsSetting $setting,
    ): array {
        $bpjsWageBase = max(0, $basicSalary + $fixedAllowance);
        $healthWageBase = $this->capWage($bpjsWageBase, $setting->bpjs_kesehatan_wage_cap);
        $jpWageBase = $this->capWage($bpjsWageBase, $setting->batas_upah_jp);

        $healthCompany = $bpjsKesehatanActive ? $this->percentOf($healthWageBase, $setting->bpjs_kesehatan_perusahaan) : 0.0;
        $healthEmployee = $bpjsKesehatanActive ? $this->percentOf($healthWageBase, $setting->bpjs_kesehatan_karyawan) : 0.0;
        $jhtCompany = $bpjsKetenagakerjaanActive ? $this->percentOf($bpjsWageBase, $setting->jht_perusahaan) : 0.0;
        $jhtEmployee = $bpjsKetenagakerjaanActive ? $this->percentOf($bpjsWageBase, $setting->jht_karyawan) : 0.0;
        $jpCompany = $bpjsKetenagakerjaanActive ? $this->percentOf($jpWageBase, $setting->jp_perusahaan) : 0.0;
        $jpEmployee = $bpjsKetenagakerjaanActive ? $this->percentOf($jpWageBase, $setting->jp_karyawan) : 0.0;
        $jkk = $bpjsKetenagakerjaanActive ? $this->percentOf($bpjsWageBase, $setting->jkkRateFor($riskCode)) : 0.0;
        $jkm = $bpjsKetenagakerjaanActive ? $this->percentOf($bpjsWageBase, $setting->jkm) : 0.0;

        return [
            'bpjs_wage_base' => $this->money($bpjsWageBase),
            'health_wage_base' => $this->money($healthWageBase),
            'jp_wage_base' => $this->money($jpWageBase),
            'bpjs_kesehatan_perusahaan' => $healthCompany,
            'bpjs_kesehatan_karyawan' => $healthEmployee,
            'jht_perusahaan' => $jhtCompany,
            'jht_karyawan' => $jhtEmployee,
            'jp_perusahaan' => $jpCompany,
            'jp_karyawan' => $jpEmployee,
            'jkk' => $jkk,
            'jkm' => $jkm,
            'total_bpjs_perusahaan' => $this->money($healthCompany + $jhtCompany + $jpCompany + $jkk + $jkm),
            'total_bpjs_karyawan' => $this->money($healthEmployee + $jhtEmployee + $jpEmployee),
            'risk_code' => $riskCode,
            // Snapshot membuat audit payroll tetap akurat setelah tarif diubah.
            'setting_snapshot' => [
                'setting_id' => $setting->id,
                'effective_from' => $setting->effective_from?->toDateString(),
                'risk_code' => $riskCode,
                'rates' => [
                    'bpjs_kesehatan_perusahaan' => (float) $setting->bpjs_kesehatan_perusahaan,
                    'bpjs_kesehatan_karyawan' => (float) $setting->bpjs_kesehatan_karyawan,
                    'jht_perusahaan' => (float) $setting->jht_perusahaan,
                    'jht_karyawan' => (float) $setting->jht_karyawan,
                    'jp_perusahaan' => (float) $setting->jp_perusahaan,
                    'jp_karyawan' => (float) $setting->jp_karyawan,
                    'jkk' => $setting->jkkRateFor($riskCode),
                    'jkm' => (float) $setting->jkm,
                ],
                'caps' => [
                    'bpjs_kesehatan_wage_cap' => $setting->bpjs_kesehatan_wage_cap,
                    'batas_upah_jp' => $setting->batas_upah_jp,
                ],
            ],
        ];
    }

    /** Menyatukan komponen BPJS dengan potongan non-BPJS payroll. */
    public function finalizePayroll(float $grossIncome, float $otherDeductions, array $bpjs): array
    {
        $totalDeduction = $this->money($otherDeductions + $bpjs['total_bpjs_karyawan']);

        return [
            'total_deduction' => $totalDeduction,
            'take_home_pay' => $this->money(max(0, $grossIncome - $totalDeduction)),
            'total_company_burden' => $this->money($grossIncome + $bpjs['total_bpjs_perusahaan']),
        ];
    }

    private function capWage(float $wage, mixed $cap): float
    {
        $cap = (float) $cap;

        return $cap > 0 ? min($wage, $cap) : $wage;
    }

    private function percentOf(float $base, mixed $rate): float
    {
        return $this->money($base * ((float) $rate / 100));
    }

    private function money(float $value): float
    {
        return round($value, 2);
    }
}
