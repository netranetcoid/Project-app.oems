<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class AttendanceShift extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'gps_required' => 'boolean',
        'selfie_required' => 'boolean',
        'photo_required' => 'boolean',
        'allow_overtime' => 'boolean',
        'settings' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'status')) {
            $query->where('status', 'active');
        }

        return $query;
    }

    public function scopeCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /** Day Off is data-driven, so it is safe for existing integrations. */
    public function getIsDayOffAttribute(): bool
    {
        return (bool) (($this->settings ?? [])['day_off'] ?? false)
            || strtoupper((string) $this->code) === 'OFF';
    }
}
