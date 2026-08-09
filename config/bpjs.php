<?php

return [
    /*
     * Preset PPU badan usaha. Nilai ini hanya dipakai ketika Developer/HR
     * menekan "Kembalikan ke Default Resmi". Payroll tetap membaca database.
     * Verifikasi terakhir: 9 Agustus 2026.
     */
    'official_defaults' => [
        'bpjs_kesehatan_perusahaan' => 4,
        'bpjs_kesehatan_karyawan' => 1,
        'bpjs_kesehatan_wage_cap' => 12000000,
        'jht_perusahaan' => 3.7,
        'jht_karyawan' => 2,
        'jp_perusahaan' => 2,
        'jp_karyawan' => 1,
        'batas_upah_jp' => 10547400,
        'jkm' => 0.3,
        'jkk_sangat_rendah' => 0.24,
        'jkk_rendah' => 0.54,
        'jkk_sedang' => 0.89,
        'jkk_tinggi' => 1.27,
        'jkk_sangat_tinggi' => 1.74,
        'default_jkk_risk_code' => 'rendah',
        'aktif' => true,
    ],
    'official_reference' => 'PPU badan usaha: BPJS Kesehatan 4%+1%, batas upah Rp12 juta; BPJS TK JHT 3,7%+2%, JP 2%+1%, JKM 0,3%, dan JKK sesuai tingkat risiko. Verifikasi terakhir 09-08-2026.',
];