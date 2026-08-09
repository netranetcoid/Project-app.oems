<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EbupotVendor extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $casts = ['default_tax_rate' => 'decimal:4', 'has_tax_facility' => 'boolean', 'is_active' => 'boolean'];

    public function scopeForCompany(Builder $query, int $companyId): Builder { return $query->where('company_id', $companyId); }
    public function records(): HasMany { return $this->hasMany(EbupotVendorRecord::class); }
}
