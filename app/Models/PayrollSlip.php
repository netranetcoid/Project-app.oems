<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollSlip extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'basic_salary' => 'decimal:2', 'fixed_allowance' => 'decimal:2',
        'meal_allowance' => 'decimal:2', 'transport_allowance' => 'decimal:2',
        'position_allowance' => 'decimal:2', 'other_income' => 'decimal:2',
        'gross_income' => 'decimal:2', 'attendance_deduction' => 'decimal:2',
        'receivable_deduction' => 'decimal:2', 'other_deduction' => 'decimal:2',
        'total_deduction' => 'decimal:2', 'net_pay' => 'decimal:2',
        'bpjs_wage_base' => 'decimal:2',
        'bpjs_kesehatan_perusahaan' => 'decimal:2', 'bpjs_kesehatan_karyawan' => 'decimal:2',
        'jht_perusahaan' => 'decimal:2', 'jht_karyawan' => 'decimal:2',
        'jp_perusahaan' => 'decimal:2', 'jp_karyawan' => 'decimal:2',
        'jkk' => 'decimal:2', 'jkm' => 'decimal:2', 'total_company_burden' => 'decimal:2',
        'kpi_bonus' => 'decimal:2', 'kpi_payment_date' => 'date',
        'approved_at' => 'datetime', 'published_at' => 'datetime',
        'calculation_snapshot' => 'array',
    ];

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function period(): BelongsTo { return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function items(): HasMany { return $this->hasMany(PayrollSlipItem::class); }
}
