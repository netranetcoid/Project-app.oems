<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeContract extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [

        'start_date' => 'date',

        'end_date' => 'date',

        'probation_end_date' => 'date',

        'approved_at' => 'datetime',

        'settings' => 'array',

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
            'status',
            'active'
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class
        );
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(
            ContractType::class,
            'contract_type_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {

            'draft'      => 'secondary',

            'active'     => 'success',

            'expired'    => 'danger',

            'terminated' => 'dark',

            'extended'   => 'warning',

            default      => 'secondary',

        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {

            'draft'      => 'Draft',

            'active'     => 'Aktif',

            'expired'    => 'Expired',

            'terminated' => 'Berakhir',

            'extended'   => 'Perpanjangan',

            default      => '-',

        };
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date
            ->diffInMonths($this->end_date);
    }

    public function getRemainingDaysAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return now()->diffInDays(
            $this->end_date,
            false
        );
    }

    public function getIsExpiredAttribute(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return $this->end_date->isPast();
    }

    public function getNeedReminderAttribute(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return now()->diffInDays(
            $this->end_date,
            false
        ) <= 30;
    }
}
