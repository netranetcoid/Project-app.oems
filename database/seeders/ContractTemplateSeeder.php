<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ContractType;
use Illuminate\Database\Seeder;

class ContractTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'code' => 'PROBATION',
                'name' => 'PKWTT dengan Masa Percobaan 3 Bulan',
                'template_key' => 'probation',
                'legal_basis' => 'PKWTT dengan masa percobaan; wajib review HR/legal sebelum diterbitkan.',
                'default_duration_month' => 3,
                'is_probation' => true,
                'is_permanent' => true,
                'description' => 'Hubungan kerja PKWTT dengan masa percobaan paling lama tiga bulan. Tanggal akhir pada sistem adalah tanggal evaluasi, bukan tanggal berakhir otomatisnya hubungan kerja.',
                'default_addendum' => 'Hubungan kerja ini bersifat PKWTT dengan masa percobaan/evaluasi selama [[duration_month]] bulan sejak [[start_date]] sampai dengan [[probation_end_date]]. Masa percobaan tidak menghapus hak minimum pekerja dan bukan PKWT. Hasil evaluasi menjadi dasar pembinaan dan keputusan hubungan kerja sesuai peraturan yang berlaku.',
            ],
            [
                'code' => 'PKWT-1',
                'name' => 'PKWT Tahap 1',
                'template_key' => 'pkwt_1',
                'legal_basis' => 'PP Nomor 35 Tahun 2021; review jenis pekerjaan, jangka waktu, dan kompensasi PKWT.',
                'default_duration_month' => 12,
                'description' => 'Perjanjian kerja waktu tertentu tahap pertama. Durasi dapat diubah sesuai kebutuhan dan hasil review HR.',
                'default_addendum' => 'Perjanjian ini merupakan PKWT Tahap 1 dengan jangka waktu [[duration_month]] bulan. Perpanjangan atau perubahan status hanya dilakukan melalui dokumen resmi yang disetujui para pihak.',
            ],
            [
                'code' => 'PKWT-2',
                'name' => 'PKWT Tahap 2',
                'template_key' => 'pkwt_2',
                'legal_basis' => 'PP Nomor 35 Tahun 2021; review perpanjangan, kompensasi, dan persetujuan owner/HR.',
                'default_duration_month' => 12,
                'description' => 'Perjanjian kerja waktu tertentu tahap kedua/perpanjangan. Wajib direview HR sebelum diterbitkan.',
                'default_addendum' => 'Perjanjian ini merupakan PKWT Tahap 2/perpanjangan. Para pihak memahami bahwa jangka waktu, kompensasi, dan pengakhiran mengikuti peraturan ketenagakerjaan yang berlaku serta persetujuan owner.',
            ],
            [
                'code' => 'MAGANG',
                'name' => 'Pemagangan',
                'template_key' => 'internship',
                'legal_basis' => 'Permenaker Nomor 6 Tahun 2020; gunakan dokumen program pemagangan terpisah.',
                'default_duration_month' => 6,
                'description' => 'Perjanjian pemagangan dengan tujuan pembelajaran dan pengembangan kompetensi. HR wajib memastikan dokumen program magang dan hak peserta tersedia.',
                'default_addendum' => 'Program ini adalah pemagangan selama [[duration_month]] bulan untuk tujuan pembelajaran dan pengembangan kompetensi. Uang saku/fasilitas mengikuti kebijakan program dan dokumen pemagangan yang disetujui. Status peserta tidak boleh diproses sebagai PKWT tanpa review HR/legal.',
            ],
        ];

        Company::query()->each(function (Company $company) use ($definitions): void {
            foreach ($definitions as $definition) {
                $addendum = $definition['default_addendum'];
                unset($definition['default_addendum']);

                ContractType::updateOrCreate(
                    ['company_id' => $company->id, 'code' => $definition['code']],
                    $definition + [
                        'company_id' => $company->id,
                        'color' => $definition['template_key'] === 'internship' ? 'info' : 'primary',
                        'is_active' => true,
                        'settings' => ['default_addendum' => $addendum],
                    ]
                );
            }
        });
    }
}
