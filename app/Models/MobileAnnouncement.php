<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Pengumuman singkat yang ditampilkan pada beranda OvallHR.
 *
 * Data ini terpisah dari release note APK agar HR dapat mengirim informasi
 * operasional tanpa harus membuat versi aplikasi baru.
 */
class MobileAnnouncement extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'message',
        'is_active',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** Batasi response APK pada pengumuman yang sah dibaca hari ini. */
    public function scopeActiveForMobile(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('published_at', '<=', now())
            ->where(fn (Builder $expires) => $expires
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()));
    }
}
