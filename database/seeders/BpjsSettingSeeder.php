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
                ...config('bpjs.official_defaults'),
                'effective_from' => now()->toDateString(),
                'notes' => config('bpjs.official_reference'),
            ]);
        }
    }
}
