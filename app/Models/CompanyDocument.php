<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Template surat/SOP perusahaan yang dikelola dari Master Dokumen.
 * Dokumen transaksi yang telah dikirim perlu dikembangkan sebagai modul
 * arsip terpisah; model ini sengaja hanya menyimpan master template.
 */
class CompanyDocument extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'template_version' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
