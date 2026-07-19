<?php

namespace Database\Seeders;

use App\Models\ContractType;
use App\Services\Contract\ContractMasterReference;
use App\Services\Contract\ContractPrintMasterTemplate;
use Illuminate\Database\Seeder;

/**
 * Mengganti hanya draf otomatis lama (5 pasal) dengan 15 pasal yang mengikuti
 * format cetak HR. Naskah yang sudah diedit owner/HR tidak pernah disentuh.
 */
class ContractPrintMasterSeeder extends Seeder
{
    public function run(
        ContractMasterReference $references,
        ContractPrintMasterTemplate $templates
    ): void {
        ContractType::query()->each(function (ContractType $type) use ($references, $templates): void {
            $reference = $references->find($type->template_key);
            if (!$reference) {
                return;
            }

            $current = trim((string) $type->template_body);
            $legacyAutomaticBody = trim((string) ($reference['body'] ?? ''));

            // Kosong atau sama persis dengan draf otomatis lama berarti aman
            // dinormalisasi. Naskah hasil edit manual tidak akan cocok persis.
            if ($current !== '' && $current !== $legacyAutomaticBody) {
                return;
            }

            $type->template_body = $templates->bodyFor($type->template_key);
            $type->template_version = max(1, (int) $type->template_version + ($current !== '' ? 1 : 0));
            $type->save();
        });
    }
}
