<?php

namespace App\Services\Contract;

/**
 * Read-only reference drafts for the four approved company contract masters.
 *
 * These are a professional working framework, not a substitute for legal
 * counsel. HR edits and saves the company-owned document in Contract Master;
 * employee contracts only receive a frozen snapshot of that approved master.
 */
class ContractMasterReference
{
    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return [
            'probation' => [
                'label' => 'PKWTT dengan Masa Percobaan 3 Bulan',
                'legal_basis' => 'Kerangka PKWTT dengan masa percobaan; lakukan review HR/legal atas ketentuan ketenagakerjaan yang berlaku sebelum penerbitan.',
                'default_duration_month' => 3,
                'is_probation' => true,
                'is_permanent' => true,
                'official_references' => [
                    ['label' => 'PP 35 Tahun 2021', 'url' => 'https://peraturan.bpk.go.id/Details/161904/pp-no-35-tahun-2021', 'note' => 'PKWT, waktu kerja, istirahat, lembur, dan PHK.'],
                ],
                'mandatory_checklist' => [
                    'Status hubungan kerja PKWTT tercantum jelas.',
                    'Masa percobaan dan tanggal evaluasi ditulis dengan jelas.',
                    'Jabatan, penempatan, upah, waktu kerja, dan SOP dicantumkan.',
                    'Hak normatif dan mekanisme perubahan/berakhirnya hubungan kerja direview HR.',
                    'Identitas para pihak, tanggal, dan tanda tangan lengkap.',
                ],
                'body' => <<<'TEXT'
PERJANJIAN KERJA WAKTU TIDAK TERTENTU (PKWTT)
DENGAN MASA PERCOBAAN

Nomor: [[contract_no]]

Pada hari ini para pihak sepakat mengikatkan diri dalam hubungan kerja antara PT Ovall Solusindo Mandiri sebagai Pihak Pertama dan [[employee_name]] (NIK Pegawai: [[employee_no]]) sebagai Pihak Kedua.

PASAL 1 — JABATAN, PENEMPATAN, DAN HUBUNGAN KERJA
Pihak Kedua dipekerjakan pada jabatan [[position_name]] pada unit [[division_name]] / [[branch_name]]. Hubungan kerja bersifat PKWTT dan dimulai pada [[start_date]].

PASAL 2 — MASA PERCOBAAN DAN EVALUASI
Masa percobaan/evaluasi berlangsung paling lama 3 (tiga) bulan, sampai dengan [[probation_end_date]]. Evaluasi tidak menghapus hak minimum pekerja dan hasilnya ditindaklanjuti sesuai ketentuan perusahaan serta peraturan yang berlaku.

PASAL 3 — HAK, UPAH, DAN FASILITAS
Pihak Kedua menerima upah pokok sebesar Rp [[basic_salary]] serta komponen lain apabila ditetapkan dalam kebijakan tertulis perusahaan. Pembayaran, potongan, dan fasilitas mengikuti ketentuan yang berlaku.

PASAL 4 — WAKTU KERJA, TUGAS, DAN TATA TERTIB
Pihak Kedua menjalankan tugas sesuai SOP, jadwal kerja, kebijakan keselamatan, kerahasiaan data, dan tata tertib perusahaan yang berlaku.

PASAL 5 — PENUTUP
Perubahan perjanjian hanya sah apabila dibuat tertulis dan disetujui para pihak. Dokumen ini ditinjau HR/legal sebelum ditandatangani.
TEXT,
            ],
            'pkwt_1' => [
                'label' => 'PKWT Tahap 1',
                'legal_basis' => 'Kerangka PKWT merujuk pada PP Nomor 35 Tahun 2021. HR/legal wajib memverifikasi jenis pekerjaan, jangka waktu, kompensasi, dan ketentuan turunan yang berlaku.',
                'default_duration_month' => 12,
                'is_probation' => false,
                'is_permanent' => false,
                'official_references' => [
                    ['label' => 'PP 35 Tahun 2021', 'url' => 'https://peraturan.bpk.go.id/Details/161904/pp-no-35-tahun-2021', 'note' => 'Acuan utama PKWT, jangka waktu, kompensasi, waktu kerja, dan istirahat.'],
                ],
                'mandatory_checklist' => [
                    'Jenis/sifat pekerjaan PKWT telah direview dan dijelaskan.',
                    'Jangka waktu serta tanggal mulai dan berakhir tercantum.',
                    'Jabatan, penempatan, upah, waktu kerja, dan kewajiban dicantumkan.',
                    'Kompensasi PKWT dan mekanisme akhir/perpanjangan direview HR/legal.',
                    'Identitas para pihak, tanggal, dan tanda tangan lengkap.',
                ],
                'body' => <<<'TEXT'
PERJANJIAN KERJA WAKTU TERTENTU (PKWT) — TAHAP 1

Nomor: [[contract_no]]

PT Ovall Solusindo Mandiri sebagai Pihak Pertama dan [[employee_name]] (NIK Pegawai: [[employee_no]]) sebagai Pihak Kedua sepakat mengadakan PKWT.

PASAL 1 — PEKERJAAN DAN PENEMPATAN
Pihak Kedua melaksanakan pekerjaan pada jabatan [[position_name]] di [[division_name]] / [[branch_name]] sesuai uraian pekerjaan dan SOP yang berlaku.

PASAL 2 — JANGKA WAKTU
PKWT berlaku sejak [[start_date]] sampai [[end_date]] selama [[duration_month]] bulan. Jenis pekerjaan dan jangka waktu telah direview HR/legal sesuai peraturan yang berlaku.

PASAL 3 — UPAH DAN HAK
Upah pokok Pihak Kedua adalah Rp [[basic_salary]]. Hak, fasilitas, waktu kerja, istirahat, serta kompensasi akhir PKWT mengikuti peraturan yang berlaku dan kebijakan tertulis perusahaan.

PASAL 4 — KEWAJIBAN DAN KERAHASIAAN
Pihak Kedua wajib mematuhi SOP, menjaga aset serta informasi perusahaan, dan menyelesaikan pekerjaan secara profesional.

PASAL 5 — PERPANJANGAN ATAU BERAKHIRNYA PKWT
Setiap perpanjangan, pembaruan, atau pengakhiran dituangkan dalam dokumen resmi dan ditinjau HR/legal sebelum efektif.
TEXT,
            ],
            'pkwt_2' => [
                'label' => 'PKWT Tahap 2 / Perpanjangan',
                'legal_basis' => 'Kerangka PKWT/perpanjangan merujuk pada PP Nomor 35 Tahun 2021. Wajib review HR/legal dan persetujuan sesuai delegasi perusahaan.',
                'default_duration_month' => 12,
                'is_probation' => false,
                'is_permanent' => false,
                'official_references' => [
                    ['label' => 'PP 35 Tahun 2021', 'url' => 'https://peraturan.bpk.go.id/Details/161904/pp-no-35-tahun-2021', 'note' => 'Acuan PKWT dan review jangka waktu/perpanjangan.'],
                ],
                'mandatory_checklist' => [
                    'Kontrak sebelumnya dan alasan kelanjutan telah direview.',
                    'Jenis pekerjaan serta jangka waktu kelanjutan tercantum.',
                    'Hak, upah, kompensasi, dan perubahan status direview HR/legal.',
                    'Tidak ada pasal yang mengabaikan hak pekerja yang berlaku.',
                    'Identitas para pihak, tanggal, dan tanda tangan lengkap.',
                ],
                'body' => <<<'TEXT'
PERJANJIAN KERJA WAKTU TERTENTU (PKWT)
TAHAP 2 / PERPANJANGAN

Nomor: [[contract_no]]

PT Ovall Solusindo Mandiri sebagai Pihak Pertama dan [[employee_name]] (NIK Pegawai: [[employee_no]]) sebagai Pihak Kedua sepakat atas PKWT Tahap 2/Perpanjangan.

PASAL 1 — DASAR DAN PEKERJAAN
Perjanjian ini merupakan kelanjutan hubungan kerja untuk jabatan [[position_name]] pada [[division_name]] / [[branch_name]], berdasarkan kebutuhan pekerjaan yang telah direview perusahaan.

PASAL 2 — JANGKA WAKTU
Perjanjian berlaku dari [[start_date]] sampai [[end_date]] selama [[duration_month]] bulan. Dokumen ini tidak menggantikan kewajiban review atas syarat PKWT dan kompensasi yang berlaku.

PASAL 3 — UPAH, HAK, DAN KEWAJIBAN
Upah pokok Pihak Kedua adalah Rp [[basic_salary]]. Hak dan kewajiban mengikuti peraturan yang berlaku, SOP perusahaan, serta ketentuan tertulis yang menjadi bagian tidak terpisahkan dari perjanjian ini.

PASAL 4 — PENGAKHIRAN DAN ADMINISTRASI
Masa berakhir, perpanjangan, atau perubahan status hanya diproses melalui dokumen resmi, persetujuan berwenang, dan perhitungan hak sesuai ketentuan yang berlaku.

PASAL 5 — PENUTUP
Setiap perubahan pasal harus dibuat tertulis dan disetujui para pihak setelah review HR/legal.
TEXT,
            ],
            'internship' => [
                'label' => 'Perjanjian Pemagangan',
                'legal_basis' => 'Kerangka pemagangan mengacu pada Permenaker Nomor 6 Tahun 2020 tentang Penyelenggaraan Pemagangan di Dalam Negeri. HR wajib memastikan program, pembimbing, fasilitas, dan dokumen pendukung tersedia.',
                'default_duration_month' => 6,
                'is_probation' => false,
                'is_permanent' => false,
                'official_references' => [
                    ['label' => 'Permenaker 6 Tahun 2020', 'url' => 'https://jdih.kemnaker.go.id/peraturan/detail/1658/peraturan-menteri-nomor-6-tahun-2020', 'note' => 'Penyelenggaraan pemagangan di dalam negeri.'],
                    ['label' => 'PP 35 Tahun 2021', 'url' => 'https://peraturan.bpk.go.id/Details/161904/pp-no-35-tahun-2021', 'note' => 'Referensi umum waktu kerja/istirahat apabila relevan pada pengaturan operasional.'],
                ],
                'mandatory_checklist' => [
                    'Program, kurikulum, tujuan, dan jangka waktu pemagangan tersedia.',
                    'Pembimbing/instruktur serta lokasi program ditetapkan.',
                    'Hak peserta, uang saku/fasilitas, K3, dan tata tertib dicantumkan.',
                    'Evaluasi, buku kegiatan, serta dokumen program disiapkan HR.',
                    'Status pemagangan tidak dicampur dengan PKWT/PKWTT.',
                    'Identitas para pihak, tanggal, dan tanda tangan lengkap.',
                ],
                'body' => <<<'TEXT'
PERJANJIAN PEMAGANGAN

Nomor: [[contract_no]]

PT Ovall Solusindo Mandiri sebagai Penyelenggara dan [[employee_name]] sebagai Peserta sepakat mengikuti program pemagangan.

PASAL 1 — TUJUAN DAN PROGRAM
Program bertujuan membangun pengetahuan, keterampilan, dan kompetensi pada bidang [[position_name]] di [[division_name]] / [[branch_name]]. Program, kurikulum, dan pembimbing ditetapkan dalam dokumen program pemagangan.

PASAL 2 — JANGKA WAKTU
Pemagangan berlangsung dari [[start_date]] sampai [[end_date]] selama [[duration_month]] bulan, dengan evaluasi sesuai program.

PASAL 3 — HAK DAN KEWAJIBAN PESERTA
Peserta memperoleh pembimbingan, perlindungan keselamatan, serta uang saku/fasilitas sesuai program dan kebijakan tertulis. Peserta wajib mengikuti jadwal, tata tertib, dan ketentuan keselamatan.

PASAL 4 — STATUS PROGRAM
Pemagangan adalah program pelatihan. Dokumen ini tidak boleh digunakan untuk mengubah status peserta menjadi PKWT atau PKWTT tanpa proses dan dokumen hubungan kerja yang sesuai.

PASAL 5 — PENUTUP
Penyelenggara menyimpan dokumen program, evaluasi, dan bukti pelaksanaan. Draf ini ditinjau HR/legal sebelum ditandatangani.
TEXT,
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    public function find(?string $key): ?array
    {
        return $key ? ($this->all()[$key] ?? null) : null;
    }
}
