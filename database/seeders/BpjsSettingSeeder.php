<?php

namespace Database\Seeders;

use App\Models\BpjsSetting;
use App\Models\Company;
use Illuminate\Database\Seeder;

/**
 * Nilai awal resmi yang dapat HR edit sewaktu-waktu lewat BPJS Calculation
 * Engine. firstOrCreate menjaga perubahan konfigurasi perusahaan tidak tertimpa.
 */
class BpjsSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Company::query()->active()->cursor() as $company) {
            BpjsSetting::firstOrCreate(['company_id' => $company->id], [
                'bpjs_kesehatan_perusahaan' => 4,
                'bpjs_kesehatan_karyawan' => 1,
                'bpjs_kesehatan_wage_cap' => null,
                'jht_perusahaan' => 3.7,
                'jht_karyawan' => 2,
                'jp_perusahaan' => 2,
                'jp_karyawan' => 1,
                // Batas upah JP dicatat sebagai master agar cukup diubah HR
                // apabila terdapat regulasi/pengumuman baru.
                'batas_upah_jp' => 10547400,
                'jkm' => 0.3,
                'jkk_sangat_rendah' => 0.24,
                'jkk_rendah' => 0.54,
                'jkk_sedang' => 0.89,
                'jkk_tinggi' => 1.27,
                'jkk_sangat_tinggi' => 1.74,
                'default_jkk_risk_code' => 'rendah',
                'aktif' => true,
                'effective_from' => now()->toDateString(),
                'notes' => 'Konfigurasi awal. Verifikasi berkala terhadap ketentuan BPJS yang berlaku.',
            ]);
        }
    }
}
