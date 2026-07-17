<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'cutoff_date' => 'date',
        'salary_payment_date' => 'date',
        'kpi_payment_date' => 'date',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'settings_snapshot' => 'array',
    ];

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function slips(): HasMany { return $this->hasMany(PayrollSlip::class); }
}
