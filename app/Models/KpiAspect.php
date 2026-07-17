<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiAspect extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function scopeForCompany(Builder $query, int $companyId): Builder { return $query->where('company_id', $companyId); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
}
