<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

/**
 * Konfigurasi tarif BPJS per perusahaan. Model ini hanya memetakan data;
 * perhitungan nominal sengaja berada di PayrollCalculationService.
 */
class BpjsSetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'bpjs_kesehatan_perusahaan' => 'decimal:4',
        'bpjs_kesehatan_karyawan' => 'decimal:4',
        'bpjs_kesehatan_wage_cap' => 'decimal:2',
        'jht_perusahaan' => 'decimal:4',
        'jht_karyawan' => 'decimal:4',
        'jp_perusahaan' => 'decimal:4',
        'jp_karyawan' => 'decimal:4',
        'batas_upah_jp' => 'decimal:2',
        'jkm' => 'decimal:4',
        'jkk_sangat_rendah' => 'decimal:4',
        'jkk_rendah' => 'decimal:4',
        'jkk_sedang' => 'decimal:4',
        'jkk_tinggi' => 'decimal:4',
        'jkk_sangat_tinggi' => 'decimal:4',
        'aktif' => 'boolean',
        'effective_from' => 'date',
    ];

    public const RISK_RATE_COLUMNS = [
        'sangat_rendah' => 'jkk_sangat_rendah',
        'rendah' => 'jkk_rendah',
        'sedang' => 'jkk_sedang',
        'tinggi' => 'jkk_tinggi',
        'sangat_tinggi' => 'jkk_sangat_tinggi',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    /** Nilai rate JKK selalu dibaca dari kolom konfigurasi, bukan konstanta kode. */
    public function jkkRateFor(string $riskCode): float
    {
        $column = self::RISK_RATE_COLUMNS[$riskCode] ?? null;

        if (! $column) {
            throw new InvalidArgumentException('Kategori risiko JKK tidak dikenal.');
        }

        return (float) $this->getAttribute($column);
    }

    public static function riskOptions(): array
    {
        return [
            'sangat_rendah' => 'Sangat Rendah',
            'rendah' => 'Rendah',
            'sedang' => 'Sedang',
            'tinggi' => 'Tinggi',
            'sangat_tinggi' => 'Sangat Tinggi',
        ];
    }
}
