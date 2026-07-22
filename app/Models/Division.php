<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Division extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'parent_id');
    }

    /** Operational scope: null means a company-wide/shared division. */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Division::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'division_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'division_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'is_active')) {
            $query->where($this->getTable() . '.is_active', true);
        }

        if (Schema::hasColumn($this->getTable(), 'status')) {
            $query->where($this->getTable() . '.status', 'active');
        }

        return $query;
    }
}
