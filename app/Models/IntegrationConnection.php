<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationConnection extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_enabled' => 'boolean',
        'allow_inbound' => 'boolean',
        'allow_outbound' => 'boolean',
        'verify_tls' => 'boolean',
        'cutover_at' => 'datetime',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
    ];

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function outboxEvents(): HasMany
    {
        return $this->hasMany(IntegrationOutbox::class);
    }
}

