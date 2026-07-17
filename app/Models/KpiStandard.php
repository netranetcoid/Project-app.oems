<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiStandard extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $casts = ['bonus_maximum' => 'decimal:2', 'is_active' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }
    public function items(): HasMany { return $this->hasMany(KpiStandardItem::class)->orderBy('sort_order'); }
    public function scopeForCompany(Builder $query, int $companyId): Builder { return $query->where('company_id', $companyId); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
}
