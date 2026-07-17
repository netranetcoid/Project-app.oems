<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeReceivable extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'first_deduction_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function sourceRequest(): BelongsTo { return $this->belongsTo(EmployeeRequest::class, 'source_request_id'); }
}
