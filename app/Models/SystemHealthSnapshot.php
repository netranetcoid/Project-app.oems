<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SystemHealthSnapshot extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'metrics' => 'array',
        'checked_at' => 'datetime',
    ];

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}

