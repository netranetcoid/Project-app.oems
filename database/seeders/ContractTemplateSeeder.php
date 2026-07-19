<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ContractType;
use App\Services\Contract\ContractMasterReference;
use Illuminate\Database\Seeder;

/**
 * Ensures the four company-approved masters exist.
 *
 * This is deliberately non-destructive: a document edited in Master Kontrak
 * is never overwritten when the seeder is run again on a live company.
 */
class ContractTemplateSeeder extends Seeder
{
    public function run(ContractMasterReference $references): void
    {
        $definitions = [
            'probation' => ['code' => 'PROBATION', 'name' => 'PKWTT dengan Masa Percobaan 3 Bulan', 'color' => 'primary'],
            'pkwt_1' => ['code' => 'PKWT-1', 'name' => 'PKWT Tahap 1', 'color' => 'primary'],
            'pkwt_2' => ['code' => 'PKWT-2', 'name' => 'PKWT Tahap 2', 'color' => 'primary'],
            'internship' => ['code' => 'MAGANG', 'name' => 'Pemagangan', 'color' => 'info'],
        ];

        Company::query()->each(function (Company $company) use ($definitions, $references): void {
            foreach ($definitions as $key => $definition) {
                $reference = $references->find($key);
                if (!$reference) {
                    continue;
                }

                $type = ContractType::firstOrNew([
                    'company_id' => $company->id,
                    'code' => $definition['code'],
                ]);

                // Fill only absent data. This protects the editable document
                // owned by HR/owner after the first save.
                $type->name = $type->name ?: $definition['name'];
                $type->template_key = $type->template_key ?: $key;
                $type->legal_basis = $type->legal_basis ?: $reference['legal_basis'];
                // Jangan menyuntikkan draf generik ke master baru. Naskah
                // hanya berasal dari editor Master Kontrak; bila kosong,
                // format cetak HR legacy tetap menjadi fallback yang aman.
                $type->default_duration_month = $type->default_duration_month ?: $reference['default_duration_month'];
                $type->color = $type->color ?: $definition['color'];
                $type->is_probation = $type->exists ? $type->is_probation : $reference['is_probation'];
                $type->is_permanent = $type->exists ? $type->is_permanent : $reference['is_permanent'];
                $type->is_active = $type->exists ? $type->is_active : true;

                $type->save();
            }
        });
    }
}
