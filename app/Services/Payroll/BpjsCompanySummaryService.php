<?php

namespace App\Services\Payroll;

use App\Models\BpjsSetting;
use App\Models\Employee;

/**
 * Rekap BPJS seluruh pegawai aktif untuk dashboard HR.
 *
 * Semua nominal tetap dihitung oleh PayrollCalculationService agar layar
 * rekap, simulasi, dan payroll tidak dapat memakai rumus yang berbeda.
 */
final class BpjsCompanySummaryService
{
    public function __construct(private readonly PayrollCalculationService $calculator) {}

    public function forCompany(int $companyId, BpjsSetting $setting): array
    {
        $employees = Employee::query()
            ->forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $totals = [
            'bpjs_kesehatan_perusahaan' => 0.0,
            'jht_perusahaan' => 0.0,
            'jp_perusahaan' => 0.0,
            'jkk' => 0.0,
            'jkm' => 0.0,
            'total_bpjs_perusahaan' => 0.0,
            'total_bpjs_karyawan' => 0.0,
            'bpjs_wage_base' => 0.0,
        ];

        $rows = $employees->map(function (Employee $employee) use ($setting, &$totals): array {
            $calculation = $this->calculator->calculateForEmployee($employee, $setting);
            foreach (array_keys($totals) as $key) {
                $totals[$key] += (float) ($calculation[$key] ?? 0);
            }

            return [
                'employee' => $employee,
                'calculation' => $calculation,
                'payroll_ready' => (float) $employee->basic_salary > 0,
            ];
        });

        foreach ($totals as $key => $value) {
            $totals[$key] = round($value, 2);
        }

        return [
            'employees' => $rows,
            'totals' => $totals,
            'counts' => [
                'employees' => $employees->count(),
                'payroll_ready' => $rows->where('payroll_ready', true)->count(),
                'health_active' => $employees->filter(fn (Employee $employee): bool => (bool) $employee->is_bpjs_kesehatan_active)->count(),
                'employment_active' => $employees->filter(fn (Employee $employee): bool => (bool) $employee->is_bpjs_ketenagakerjaan_active)->count(),
            ],
        ];
    }
}
