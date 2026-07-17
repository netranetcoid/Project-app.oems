<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MobileFeatureFlag extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_enabled' => 'boolean', 'value' => 'array'];

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
