<?php

namespace App\Services\Document;

/**
 * Template awal yang bisa diedit owner/HR dari Master Dokumen.
 * Mereka adalah format operasional, bukan dokumen hukum final ataupun
 * formulir pemerintah. Pasal yang mengikat wajib melalui review berwenang.
 */
class CompanyDocumentReference
{
    public function all(): array
    {
        return [
            'sop_operasional' => $this->template('SOP-OPS', 'sop', 'SOP Operasional Divisi', 'Standar Operasional Prosedur',
                '<h2>1. TUJUAN</h2><p>Menetapkan cara kerja yang konsisten, aman, dan dapat diaudit pada [[division_name]].</p><h2>2. RUANG LINGKUP</h2><p>Dokumen ini berlaku untuk [[scope]].</p><h2>3. TANGGUNG JAWAB</h2><p>Penanggung jawab: [[owner_name]]. Pelaksana wajib mengikuti instruksi, mencatat hasil kerja, dan melaporkan kendala.</p><h2>4. PROSEDUR KERJA</h2><ol><li>Terima dan verifikasi permintaan kerja.</li><li>Lakukan pekerjaan sesuai standar keselamatan dan kualitas.</li><li>Catat hasil, bukti, dan kendala pada sistem perusahaan.</li><li>Atasan melakukan review/approval bila diperlukan.</li></ol><h2>5. DOKUMEN TERKAIT</h2><p>Form checklist, laporan kerja, dan kebijakan K3 perusahaan.</p>'),
            'sop_hr_attendance' => $this->template('SOP-HR-ABS', 'sop', 'SOP HR, Absensi & Pengajuan', 'Standar Operasional Prosedur HR',
                '<h2>1. TUJUAN</h2><p>Mengatur absensi, izin, cuti, sakit, lembur, dan approval melalui AppOEMS/OvallHR.</p><h2>2. ATURAN POKOK</h2><ol><li>Absensi dilakukan pribadi menggunakan selfie, waktu, dan lokasi sesuai kebijakan.</li><li>Izin/cuti/sakit wajib diajukan melalui aplikasi dengan bukti bila diperlukan.</li><li>Lembur dicatat mulai dan selesai serta mengikuti approval yang berlaku.</li><li>HR melakukan review dan menyimpan jejak persetujuan.</li></ol><h2>3. PENUTUP</h2><p>Perubahan SOP harus disetujui HR dan manajemen sebelum diberlakukan.</p>'),
            'mou_kemitraan' => $this->template('MOU', 'legal', 'Memorandum of Understanding (MOU)', 'Memorandum of Understanding',
                '<p>Pada tanggal [[document_date]], PT OVALL SOLUSINDO MANDIRI dan [[partner_name]] sepakat untuk menjalin kerja sama sesuai ruang lingkup berikut.</p><h2>PASAL 1 - RUANG LINGKUP</h2><p>[[scope]]</p><h2>PASAL 2 - HAK DAN KEWAJIBAN</h2><p>Masing-masing pihak menjalankan kewajiban sesuai kesepakatan tertulis dan ketentuan yang berlaku.</p><h2>PASAL 3 - KERAHASIAAN</h2><p>Informasi yang diperoleh dalam kerja sama digunakan hanya untuk tujuan kerja sama.</p><h2>PASAL 4 - JANGKA WAKTU</h2><p>Kesepakatan ini berlaku sejak [[effective_date]] sampai [[end_date]], kecuali diubah tertulis oleh para pihak.</p><p><em>Wajib review legal/owner sebelum ditandatangani.</em></p>'),
            'nota_dinas' => $this->template('ND', 'internal', 'Nota Dinas', 'NOTA DINAS',
                '<table><tr><td>Nomor</td><td>: [[document_no]]</td></tr><tr><td>Kepada</td><td>: [[recipient_name]]</td></tr><tr><td>Dari</td><td>: [[sender_name]]</td></tr><tr><td>Perihal</td><td>: [[subject]]</td></tr></table><p>Sehubungan dengan hal tersebut, mohon untuk [[instruction]].</p><p>Demikian untuk dilaksanakan dengan penuh tanggung jawab.</p>'),
            'surat_tugas' => $this->template('ST', 'letter', 'Surat Tugas', 'SURAT TUGAS',
                '<p>Nomor: [[document_no]]</p><p>Dengan ini PT OVALL SOLUSINDO MANDIRI menugaskan:</p><table><tr><td>Nama</td><td>: [[employee_name]]</td></tr><tr><td>Jabatan</td><td>: [[position_name]]</td></tr><tr><td>Tujuan/Tempat</td><td>: [[destination]]</td></tr><tr><td>Periode</td><td>: [[effective_date]] s.d. [[end_date]]</td></tr><tr><td>Keperluan</td><td>: [[scope]]</td></tr></table><p>Yang bersangkutan wajib melaksanakan tugas dengan baik dan menyampaikan laporan hasil penugasan.</p>'),
            'surat_jalan' => $this->template('SJ', 'operational', 'Surat Jalan Barang', 'SURAT JALAN',
                '<p>Nomor: [[document_no]]</p><table><tr><td>Tanggal</td><td>: [[document_date]]</td></tr><tr><td>Pengirim</td><td>: PT OVALL SOLUSINDO MANDIRI</td></tr><tr><td>Penerima</td><td>: [[recipient_name]]</td></tr><tr><td>Alamat Tujuan</td><td>: [[destination]]</td></tr></table><h2>RINCIAN BARANG</h2><table border="1" cellspacing="0" cellpadding="6"><tr><th>No</th><th>Nama Barang</th><th>Qty</th><th>Keterangan</th></tr><tr><td>1</td><td>[[item_name]]</td><td>[[quantity]]</td><td>[[notes]]</td></tr></table><p>Barang diterima dalam kondisi: [[item_condition]].</p>'),
            'berita_acara' => $this->template('BA', 'operational', 'Berita Acara', 'BERITA ACARA',
                '<p>Nomor: [[document_no]]</p><p>Pada [[document_date]], telah dilakukan [[activity_name]] di [[destination]].</p><h2>HASIL / KRONOLOGI</h2><p>[[notes]]</p><h2>TINDAK LANJUT</h2><p>[[instruction]]</p><p>Berita acara ini dibuat dengan sebenar-benarnya untuk digunakan sebagaimana mestinya.</p>'),
            'surat_keterangan_kerja' => $this->template('SKK', 'letter', 'Surat Keterangan Kerja', 'SURAT KETERANGAN KERJA',
                '<p>Nomor: [[document_no]]</p><p>Dengan ini menerangkan bahwa:</p><table><tr><td>Nama</td><td>: [[employee_name]]</td></tr><tr><td>Jabatan</td><td>: [[position_name]]</td></tr><tr><td>Divisi</td><td>: [[division_name]]</td></tr><tr><td>Masa Kerja</td><td>: [[effective_date]] s.d. [[end_date]]</td></tr></table><p>Adalah benar bekerja pada PT OVALL SOLUSINDO MANDIRI. Surat ini dibuat untuk keperluan [[scope]].</p>'),
            'surat_peringatan' => $this->template('SP', 'hr', 'Surat Peringatan (Draft HR)', 'SURAT PERINGATAN',
                '<p>Nomor: [[document_no]]</p><p>Kepada Yth. [[employee_name]]</p><p>Berdasarkan hasil pemeriksaan HR terhadap [[notes]], perusahaan menyampaikan peringatan ini sesuai peraturan perusahaan dan ketentuan yang berlaku.</p><p>Perbaikan yang wajib dilakukan: [[instruction]].</p><p><em>Dokumen ini harus direview HR/legal dan ditandatangani pejabat berwenang sebelum diterbitkan.</em></p>'),
            'bpjs_checklist' => $this->template('BPJS-READY', 'bpjs', 'Checklist Kesiapan Pendaftaran BPJS', 'CHECKLIST KESIAPAN PENDAFTARAN BPJS',
                '<h2>DATA PEMBERI KERJA</h2><p>Nama badan usaha: [[company_legal_name]]<br>NPWP: [[company_npwp]]<br>NIB/Izin usaha: [[company_nib]]<br>PIC: [[pic_name]]</p><h2>CHECKLIST F1 / F1a / F2</h2><ol><li>Data PT OSM, alamat, email dan PIC telah diverifikasi.</li><li>Data pekerja: nama, NIK, KK, tanggal mulai, upah dan status kerja telah diperiksa.</li><li>Dokumen pendukung sudah dikumpulkan dan direkonsiliasi HR/payroll.</li><li>Formulir resmi BPJS diisi/dikirim melalui kanal resmi BPJS.</li></ol><p><em>Ini adalah checklist internal, bukan formulir resmi BPJS atau bukti kepesertaan.</em></p>'),
        ];
    }

    private function template(string $code, string $category, string $name, string $subject, string $body): array
    {
        return compact('code', 'category', 'name', 'subject', 'body');
    }
}
