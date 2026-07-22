<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationOutbox extends Model
{
    protected $table = 'integration_outbox';
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'encrypted:array',
        'response_summary' => 'array',
        'next_retry_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'locked_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(IntegrationConnection::class, 'integration_connection_id');
    }
}
