<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'changed_fields' => 'array',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Audit merupakan catatan append-only. Koreksi dibuat sebagai event
        // baru dan record lama tidak boleh diubah/dihapus lewat model.
        static::updating(fn () => throw new LogicException('Audit log is immutable.'));
        static::deleting(fn () => throw new LogicException('Audit log is immutable.'));
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

