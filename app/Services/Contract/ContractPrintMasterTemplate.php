<?php

namespace App\Services\Contract;

/**
 * Satu sumber naskah master yang mengikuti struktur hasil cetak HR Kontrak.
 *
 * Kop surat, identitas para pihak, dan blok tanda tangan tetap menjadi tugas
 * hr/contracts/print. Kelas ini hanya menyediakan 15 pasal yang boleh diedit
 * HR dari Master Kontrak.
 */
class ContractPrintMasterTemplate
{
    public function bodyFor(?string $templateKey): string
    {
        $term = match ($templateKey) {
            'probation' => 'Hubungan kerja bersifat PKWTT dengan masa percobaan/evaluasi sampai dengan [[probation_end_date]]. Hasil evaluasi ditindaklanjuti sesuai ketentuan perusahaan dan peraturan yang berlaku.',
            'internship' => 'Program pemagangan berlaku sejak [[start_date]] sampai [[end_date]] selama [[duration_month]] bulan sesuai program, kurikulum, dan pembimbing yang ditetapkan perusahaan.',
            default => 'Perjanjian kerja berlaku sejak [[start_date]] sampai [[end_date]] selama [[duration_month]] bulan sesuai ketentuan PKWT dan kebutuhan operasional perusahaan.',
        };

        $payLabel = $templateKey === 'internship'
            ? 'uang saku / imbalan program'
            : 'upah dan tunjangan';

        return <<<TEXT
PASAL 1 — JABATAN DAN PENEMPATAN
PIHAK KEDUA diterima untuk menjalankan jabatan [[position_name]] pada Divisi [[division_name]] di unit/site [[branch_name]]. PIHAK KEDUA bersedia menjalankan tugas, tanggung jawab, SOP, Peraturan Perusahaan, serta instruksi atasan yang sah sesuai kebutuhan operasional perusahaan.

PASAL 2 — MASA KERJA
{$term}

PASAL 3 — WAKTU KERJA DAN KEHADIRAN
Hari dan jam kerja mengikuti jadwal kerja, shift, serta kebijakan operasional yang ditetapkan perusahaan. PIHAK KEDUA wajib melakukan absensi melalui sistem yang ditetapkan, hadir tepat waktu, dan mematuhi ketentuan istirahat, lembur, piket, atau penugasan di luar jam kerja sesuai prosedur perusahaan.

PASAL 4 — {$payLabel}
PIHAK PERTAMA memberikan {$payLabel} kepada PIHAK KEDUA sesuai ketentuan perusahaan. Gaji pokok/imbalan dasar pada saat perjanjian dibuat sebesar Rp [[basic_salary]]. Komponen lain, potongan, waktu pembayaran, serta perubahan penghasilan mengikuti slip penggajian, kebijakan tertulis perusahaan, dan ketentuan yang berlaku.

PASAL 5 — JAMINAN SOSIAL DAN KESELAMATAN KERJA
Apabila telah memenuhi persyaratan dan sesuai ketentuan yang berlaku, PIHAK KEDUA didaftarkan dalam program BPJS Kesehatan dan BPJS Ketenagakerjaan. Besaran iuran, manfaat, keselamatan kerja, serta perlindungan kerja mengikuti peraturan dan kebijakan perusahaan yang berlaku.

PASAL 6 — HAK PIHAK KEDUA
- Menerima penghasilan sesuai ketentuan dan hasil kerja.
- Mendapatkan lingkungan kerja yang aman dan perlakuan yang profesional.
- Mendapatkan fasilitas kerja sesuai jabatan dan kebutuhan operasional.
- Mendapatkan penilaian KPI secara objektif.
- Mendapatkan cuti, izin, pelatihan, atau pengembangan sesuai ketentuan perusahaan.

PASAL 7 — KEWAJIBAN PIHAK KEDUA
- Mematuhi Peraturan Perusahaan, SOP, tata tertib, dan kebijakan OEMS.
- Melaksanakan pekerjaan secara jujur, disiplin, profesional, dan bertanggung jawab.
- Menjaga nama baik, kerahasiaan, data pelanggan, serta informasi perusahaan.
- Menjaga dan menggunakan aset perusahaan dengan baik.
- Menyampaikan laporan kerja dan mengikuti evaluasi KPI yang ditetapkan.

PASAL 8 — DISIPLIN KERJA
PIHAK KEDUA wajib menjaga disiplin, mematuhi jadwal, dan tidak melakukan manipulasi absensi, laporan kerja, maupun data KPI. Pelanggaran disiplin dapat menjadi bagian penilaian kinerja dan evaluasi kelanjutan hubungan kerja sesuai prosedur perusahaan.

PASAL 9 — LARANGAN
- Menyalahgunakan wewenang, jabatan, data, atau aset perusahaan.
- Membocorkan data pelanggan, data perusahaan, atau informasi rahasia tanpa kewenangan.
- Menggunakan aset perusahaan untuk kepentingan pribadi tanpa persetujuan.
- Melakukan kekerasan, perjudian, penyalahgunaan narkoba, atau tindak pidana.
- Melakukan tindakan yang merugikan perusahaan, pelanggan, atau rekan kerja.

PASAL 10 — SANKSI DAN PEMBINAAN
Pelanggaran terhadap perjanjian, SOP, atau Peraturan Perusahaan ditindaklanjuti secara proporsional sesuai fakta dan tingkat pelanggaran. Tindakan dapat berupa pembinaan, teguran lisan, surat peringatan, skorsing apabila diperlukan, atau tindakan lain sesuai ketentuan yang berlaku.

PASAL 11 — BERAKHIRNYA HUBUNGAN KERJA
Hubungan kerja dapat berakhir karena masa perjanjian selesai, pengunduran diri, pemutusan hubungan kerja sesuai ketentuan, meninggal dunia, atau sebab lain yang diatur dalam peraturan perundang-undangan. Hak dan kewajiban para pihak diselesaikan sesuai ketentuan yang berlaku.

PASAL 12 — KEADAAN MEMAKSA
Keadaan memaksa mencakup keadaan di luar kemampuan para pihak seperti bencana alam, kebakaran, wabah, perang, gangguan sistem nasional, kebijakan pemerintah, atau keadaan lain yang secara nyata menghambat pelaksanaan kewajiban. Para pihak akan menyelesaikannya secara musyawarah sesuai ketentuan yang berlaku.

PASAL 13 — PENYELESAIAN PERSELISIHAN
Setiap perselisihan yang timbul dari pelaksanaan perjanjian ini diselesaikan terlebih dahulu secara musyawarah untuk mufakat. Bila tidak tercapai, penyelesaian dilakukan sesuai mekanisme dan peraturan perundang-undangan Republik Indonesia.

PASAL 14 — KERAHASIAAN, DATA, DAN ASET
PIHAK KEDUA wajib menjaga keamanan data, akun, dokumen, perangkat, serta aset yang digunakan selama bekerja. Pada saat hubungan kerja berakhir, PIHAK KEDUA wajib mengembalikan aset dan menyerahkan pekerjaan/data sesuai prosedur perusahaan.

PASAL 15 — PENUTUP DAN PERUBAHAN PERJANJIAN
Perjanjian ini dibuat dengan sadar tanpa paksaan dari pihak mana pun. Perubahan, tambahan, atau ketentuan khusus hanya berlaku apabila dibuat tertulis dan disetujui pihak yang berwenang. Perjanjian dibuat dalam dua rangkap yang masing-masing mempunyai kekuatan yang sama.
TEXT;
    }
}
