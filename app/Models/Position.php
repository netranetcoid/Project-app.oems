<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Position extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_approver' => 'boolean',
        'is_management' => 'boolean',
        'is_field_worker' => 'boolean',
        'is_kpi_enabled' => 'boolean',
        'is_payroll_enabled' => 'boolean',
        'default_basic_salary' => 'decimal:2',
        'default_allowance' => 'decimal:2',
        'default_kpi_incentive_max' => 'decimal:2',
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

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /** Optional operational Branch/Site scope for payroll and KPI ownership. */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Position::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'position_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
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
