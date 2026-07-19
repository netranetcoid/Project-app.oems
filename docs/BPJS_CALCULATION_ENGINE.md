# BPJS Calculation Engine

## Tujuan

Modul ini menghitung komponen BPJS dari gaji pokok, tunjangan tetap, status
kepesertaan BPJS Kesehatan/Ketenagakerjaan, dan risiko JKK. Tarif tidak
ditanam di controller, model, atau aplikasi OvallHR; semuanya dibaca dari
`bpjs_settings` per perusahaan.

## Lokasi pengaturan

`System > BPJS Calculation Engine`

HR mengubah tarif, batas upah JP, opsi batas upah kesehatan, default risiko
JKK, tanggal berlaku, dan catatan perubahan di halaman tersebut. Konfigurasi
awal dibuat oleh `BpjsSettingSeeder` dan tidak ditimpa bila seeder dijalankan
ulang.

## Rumus yang digunakan

- Dasar upah BPJS = gaji pokok + tunjangan tetap.
- JHT, JKK, dan JKM memakai dasar upah BPJS apabila BPJS Ketenagakerjaan aktif.
- JP memakai nilai terkecil antara dasar upah BPJS dan `batas_upah_jp`.
- BPJS Kesehatan memakai dasar upah BPJS, atau batas kesehatan bila HR mengisi
  batas tersebut.
- Total beban perusahaan = gaji bruto + seluruh komponen perusahaan.
- Total potongan karyawan = potongan existing (misalnya cicilan) + komponen
  BPJS karyawan.
- Take Home Pay = gaji bruto - total potongan karyawan.

## Audit dan integrasi

Saat draft payroll dibuat, tarif serta batas upah disalin ke
`payroll_slips.calculation_snapshot`. Mengubah pengaturan bulan depan tidak
mengubah nominal slip yang sudah pernah diterbitkan.

Event `payroll.period.published` ke AppBill membawa objek `bpjs` per slip:
dasar upah, seluruh komponen perusahaan/karyawan, serta total beban
perusahaan. AppBill tidak perlu mengurai nama item slip.

## Operasional

1. Lengkapi status dan nomor kepesertaan pegawai di `Human Resource > BPJS & Kepatuhan`.
2. Atur risiko JKK per pegawai bila berbeda dari default perusahaan.
3. Review tarif dan batas upah di BPJS Calculation Engine sebelum membuat draft payroll.
4. Generate payroll. Setelah HR approve/publish, histori tarif tetap terkunci di snapshot slip.

> Catatan: nilai default adalah titik awal konfigurasi. HR wajib memverifikasi
> pengumuman resmi BPJS/regulasi terbaru sebelum periode payroll diproses.
