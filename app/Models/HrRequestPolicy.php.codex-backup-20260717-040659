<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HrRequestPolicy extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'max_amount' => 'decimal:2',
        'requires_document' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
