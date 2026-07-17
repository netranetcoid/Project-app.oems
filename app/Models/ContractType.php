<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractType extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_probation' => 'boolean',
        'is_permanent' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
        'template_version' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForCompany(
        Builder $query,
        int $companyId
    ): Builder {

        return $query->where(
            'company_id',
            $companyId
        );
    }

    public function scopeActive(
        Builder $query
    ): Builder {

        return $query->where(
            'is_active',
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationship
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(
            EmployeeContract::class,
            'contract_type_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active
            ? 'success'
            : 'danger';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active
            ? 'Aktif'
            : 'Non Aktif';
    }

    public function getDurationLabelAttribute(): string
    {
        if ($this->is_permanent) {
            return 'Permanent';
        }

        if (!$this->default_duration_month) {
            return '-';
        }

        return $this->default_duration_month . ' Bulan';
    }
}
