<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\Division;
use App\Services\Document\CompanyDocumentReference;
use Illuminate\Database\Seeder;

/**
 * Menambah template awal tanpa pernah menimpa dokumen yang sudah diedit.
 * Aman dijalankan kembali untuk company baru maupun production.
 */
class CompanyDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $references = app(CompanyDocumentReference::class)->all();

        foreach (Company::query()->active()->get() as $company) {
            foreach ($references as $reference) {
                CompanyDocument::firstOrCreate(
                    ['company_id' => $company->id, 'code' => $reference['code']],
                    [
                        'category' => $reference['category'],
                        'name' => $reference['name'],
                        'subject' => $reference['subject'],
                        'description' => 'Template bawaan PT OSM. Review dan edit sebelum diterbitkan.',
                        'body' => $reference['body'],
                        'settings' => ['letterhead' => 'osm_default'],
                        'template_version' => 1,
                        'is_active' => true,
                        'is_system' => true,
                    ]
                );
            }

            // Setiap divisi aktif mendapat SOP sendiri sejak bootstrap. HR
            // kemudian mengedit isi per divisi, bukan memakai satu SOP umum
            // untuk seluruh organisasi.
            $sop = $references['sop_operasional'];
            Division::query()->forCompany($company->id)->active()->get()->each(function (Division $division) use ($company, $sop): void {
                $code = 'SOP-' . strtoupper(str_replace(' ', '-', $division->code));

                CompanyDocument::firstOrCreate(
                    ['company_id' => $company->id, 'code' => $code],
                    [
                        'category' => 'sop',
                        'name' => 'SOP ' . $division->name,
                        'subject' => 'Standar Operasional Prosedur ' . $division->name,
                        'description' => 'Template SOP khusus divisi. Edit ruang lingkup dan langkah kerja sebelum berlaku.',
                        'body' => str_replace('[[division_name]]', e($division->name), $sop['body']),
                        'settings' => ['letterhead' => 'osm_default', 'division_id' => $division->id],
                        'template_version' => 1,
                        'is_active' => true,
                        'is_system' => true,
                    ]
                );
            });
        }
    }
}
