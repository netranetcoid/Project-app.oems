<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\EmployeeKpiAssessment;
use App\Models\EmployeeReceivable;
use App\Models\BpjsSetting;
use App\Models\PayrollPeriod;
use App\Models\PayrollSlip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollService
{
    public function __construct(private PayrollCalculationService $calculator)
    {
    }

    public function generate(int $companyId, int $year, int $month, int $userId): PayrollPeriod
    {
        return DB::transaction(function () use ($companyId, $year, $month, $userId): PayrollPeriod {
            $monthDate = Carbon::create($year, $month, 1);
            $period = PayrollPeriod::firstOrCreate(
                ['company_id' => $companyId, 'period_year' => $year, 'period_month' => $month],
                [
                    'cutoff_date' => $monthDate->copy()->endOfMonth()->toDateString(),
                    'salary_payment_date' => $monthDate->copy()->endOfMonth()->toDateString(),
                    // Bonus bulan berjalan dibayar tanggal 15 bulan berikutnya.
                    'kpi_payment_date' => $monthDate->copy()->addMonthNoOverflow()->day(15)->toDateString(),
                    'status' => 'draft', 'created_by' => $userId,
                    'settings_snapshot' => ['salary_rule' => 'month_end', 'kpi_rule' => 'next_month_day_15'],
                ]
            );

            if ($period->status !== 'draft') {
                throw ValidationException::withMessages(['period' => 'Payroll yang sudah diajukan/disetujui tidak boleh dihitung ulang.']);
            }

            // Konfigurasi diambil sekali untuk seluruh periode agar setiap slip
            // konsisten, lalu disimpan kembali sebagai snapshot di masing-masing slip.
            $bpjsSetting = $this->calculator->settingForCompany($companyId);

            Employee::forCompany($companyId)->active()->with(['branch', 'position'])
                ->where('basic_salary', '>', 0)->chunkById(100, function ($employees) use ($period, $bpjsSetting): void {
                    foreach ($employees as $employee) {
                        $this->buildSlip($period, $employee, $bpjsSetting);
                    }
                });

            return $this->recalculateTotals($period);
        });
    }

    private function buildSlip(PayrollPeriod $period, Employee $employee, BpjsSetting $bpjsSetting): void
    {
        $income = [
            'basic_salary' => (float) $employee->basic_salary,
            'fixed_allowance' => (float) $employee->fixed_allowance,
            'meal_allowance' => (float) $employee->meal_allowance,
            'transport_allowance' => (float) $employee->transport_allowance,
            'position_allowance' => (float) $employee->position_allowance,
        ];
        $receivables = EmployeeReceivable::forCompany((int) $period->company_id)
            ->where('employee_id', $employee->id)->where('status', 'active')
            ->whereDate('first_deduction_date', '<=', $period->salary_payment_date)->get();
        $receivableDeduction = $receivables->sum(fn ($item) => min((float) $item->installment_amount, (float) $item->remaining_amount));
        $gross = array_sum($income);
        $bpjs = $this->calculator->calculateForEmployee($employee, $bpjsSetting);
        $payrollTotals = $this->calculator->finalizePayroll($gross, $receivableDeduction, $bpjs);

        $kpiBonus = (float) EmployeeKpiAssessment::forCompany((int) $period->company_id)
            ->where('employee_id', $employee->id)->where('period_year', $period->period_year)
            ->where('period_month', $period->period_month)->where('status', 'approved')->value('bonus_amount');

        $slip = PayrollSlip::updateOrCreate(
            ['payroll_period_id' => $period->id, 'employee_id' => $employee->id],
            [
                'company_id' => $period->company_id, 'branch_id' => $employee->branch_id,
                'slip_no' => sprintf('SLIP-%04d%02d-%s', $period->period_year, $period->period_month, $employee->employee_no),
                'employee_no_snapshot' => $employee->employee_no,
                'employee_name_snapshot' => $employee->name,
                'branch_name_snapshot' => $employee->branch?->name,
                'position_name_snapshot' => $employee->position?->name,
                'bank_name_snapshot' => $employee->bank_name,
                'bank_account_snapshot' => $employee->bank_account_no,
                ...$income,
                'gross_income' => $gross,
                'receivable_deduction' => $receivableDeduction,
                // Komponen BPJS perusahaan dan karyawan tersimpan terpisah
                // untuk slip, akuntansi AppBill, dan audit regulasi.
                'bpjs_wage_base' => $bpjs['bpjs_wage_base'],
                'bpjs_kesehatan_perusahaan' => $bpjs['bpjs_kesehatan_perusahaan'],
                'bpjs_kesehatan_karyawan' => $bpjs['bpjs_kesehatan_karyawan'],
                'jht_perusahaan' => $bpjs['jht_perusahaan'],
                'jht_karyawan' => $bpjs['jht_karyawan'],
                'jp_perusahaan' => $bpjs['jp_perusahaan'],
                'jp_karyawan' => $bpjs['jp_karyawan'],
                'jkk' => $bpjs['jkk'],
                'jkm' => $bpjs['jkm'],
                'total_company_burden' => $payrollTotals['total_company_burden'],
                'total_deduction' => $payrollTotals['total_deduction'],
                'net_pay' => $payrollTotals['take_home_pay'],
                'kpi_bonus' => $kpiBonus,
                'kpi_payment_date' => $period->kpi_payment_date,
                'status' => 'draft',
                'calculation_snapshot' => [
                    'generated_at' => now()->toIso8601String(),
                    'bpjs' => $bpjs['setting_snapshot'],
                ],
            ]
        );

        $slip->items()->delete();
        foreach ($income as $code => $amount) {
            if ($amount > 0) $slip->items()->create(['category' => 'income', 'code' => $code, 'name' => str($code)->replace('_', ' ')->title(), 'amount' => $amount]);
        }
        foreach ($receivables as $receivable) {
            $amount = min((float) $receivable->installment_amount, (float) $receivable->remaining_amount);
            $slip->items()->create([
                'employee_receivable_id' => $receivable->id, 'category' => 'deduction',
                'code' => 'receivable', 'name' => 'Cicilan ' . $receivable->receivable_no,
                'amount' => $amount, 'metadata' => ['applied' => false],
            ]);
        }
        foreach ([
            ['bpjs_kesehatan_karyawan', 'BPJS Kesehatan (Karyawan)'],
            ['jht_karyawan', 'JHT (Karyawan)'],
            ['jp_karyawan', 'JP (Karyawan)'],
        ] as [$code, $name]) {
            if ($bpjs[$code] > 0) {
                $slip->items()->create(['category' => 'deduction', 'code' => $code, 'name' => $name, 'amount' => $bpjs[$code]]);
            }
        }
        foreach ([
            ['bpjs_kesehatan_perusahaan', 'BPJS Kesehatan (Beban Perusahaan)'],
            ['jht_perusahaan', 'JHT (Beban Perusahaan)'],
            ['jp_perusahaan', 'JP (Beban Perusahaan)'],
            ['jkk', 'JKK (Beban Perusahaan)'],
            ['jkm', 'JKM (Beban Perusahaan)'],
        ] as [$code, $name]) {
            if ($bpjs[$code] > 0) {
                $slip->items()->create(['category' => 'employer_cost', 'code' => $code, 'name' => $name, 'amount' => $bpjs[$code]]);
            }
        }
    }

    public function approve(PayrollPeriod $period, int $userId): PayrollPeriod
    {
        return DB::transaction(function () use ($period, $userId): PayrollPeriod {
            $period = PayrollPeriod::query()->lockForUpdate()->findOrFail($period->id);
            if ($period->status !== 'draft' || ! $period->slips()->exists()) {
                throw ValidationException::withMessages(['status' => 'Payroll harus berstatus draft dan memiliki slip.']);
            }
            $period->slips()->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
            $period->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
            return $period->fresh('slips');
        });
    }

    public function publish(PayrollPeriod $period, int $userId): PayrollPeriod
    {
        return DB::transaction(function () use ($period, $userId): PayrollPeriod {
            $period = PayrollPeriod::query()->lockForUpdate()->findOrFail($period->id);
            if ($period->status !== 'approved') {
                throw ValidationException::withMessages(['status' => 'Payroll wajib disetujui HR sebelum diterbitkan.']);
            }

            // Saldo piutang baru dipotong saat slip diterbitkan. Flag metadata
            // mencegah potongan ganda apabila endpoint dipanggil ulang.
            foreach ($period->slips()->with('items.receivable')->get() as $slip) {
                foreach ($slip->items->where('category', 'deduction') as $item) {
                    $metadata = $item->metadata ?? [];
                    if (! $item->receivable || ($metadata['applied'] ?? false)) continue;
                    $receivable = EmployeeReceivable::query()->lockForUpdate()->find($item->employee_receivable_id);
                    if (! $receivable) continue;
                    $paid = min((float) $item->amount, (float) $receivable->remaining_amount);
                    $remaining = max(0, (float) $receivable->remaining_amount - $paid);
                    $receivable->update([
                        'paid_amount' => (float) $receivable->paid_amount + $paid,
                        'remaining_amount' => $remaining,
                        'status' => $remaining <= 0 ? 'paid' : 'active',
                    ]);
                    $item->update(['metadata' => [...$metadata, 'applied' => true, 'applied_at' => now()->toIso8601String()]]);
                }
                $slip->update(['status' => 'published', 'published_at' => now()]);
            }
            $period->update(['status' => 'published', 'published_by' => $userId, 'published_at' => now()]);
            return $period->fresh('slips');
        });
    }

    private function recalculateTotals(PayrollPeriod $period): PayrollPeriod
    {
        $totals = $period->slips()->selectRaw('COALESCE(SUM(gross_income),0) gross, COALESCE(SUM(total_deduction),0) deductions, COALESCE(SUM(net_pay),0) net, COALESCE(SUM(kpi_bonus),0) kpi, COALESCE(SUM(total_company_burden),0) company_burden')->first();
        $period->update(['total_gross' => $totals->gross, 'total_deduction' => $totals->deductions, 'total_net' => $totals->net, 'total_kpi_bonus' => $totals->kpi, 'total_company_burden' => $totals->company_burden]);
        return $period->fresh('slips');
    }
}
