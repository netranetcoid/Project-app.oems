# UI Governance — Materialize terkunci

## Keputusan owner

**Materialize bawaan proyek adalah satu-satunya template UI AppOEMS.** Keputusan ini dikunci untuk menjaga konsistensi, mengurangi ukuran asset, mencegah konflik CSS/JavaScript, dan membuat pemeliharaan jangka panjang lebih sederhana.

Ruang lingkupnya meliputi dashboard, master data, HR, payroll, kontrak, KPI, absensi, integrasi AppBill, halaman login, dan halaman baru berikutnya.

## Wajib digunakan

- Layout Blade dan partial bawaan Materialize.
- Asset Vite di `resources/assets/vendor`, `resources/assets/css`, `resources/assets/js`, dan SCSS Materialize yang sudah ada.
- Komponen, ikon, pola sidebar/navbar, tabel, formulir, modal, notifikasi, dan breakpoints responsif dari bundle Materialize proyek.
- Branding PT Ovall Solusindo Mandiri/OSM melalui layer CSS/Blade ringan tanpa mengganti fondasi template.

## Dilarang tanpa persetujuan owner + QA

- Mengganti template atau memasang UI kit/dashboard lain.
- Menambahkan CDN atau dependency UI eksternal yang menduplikasi Materialize.
- Menghapus core SCSS/JS Materialize, blank layout, atau struktur menu hanya untuk membuat halaman baru.
- Membuat tampilan yang hanya desktop dan tidak diuji pada viewport mobile.

## Pengecualian

Komponen khusus boleh dibuat bila Materialize belum menyediakan kebutuhan tersebut. Komponen harus memakai token warna, grid, typography, icon, modal, dan pola interaksi Materialize. Dependency baru harus disetujui owner, dicatat pada audit, dan diuji dampak ukuran bundle serta konflik asset-nya.

## Pemeriksaan

Jalankan `scripts/verify-materialize-governance.ps1` sebelum QA UI. Pemeriksaan menjaga marker template utama tetap ada dan mendeteksi referensi UI kit eksternal yang dilarang. Pemeriksaan ini bukan pengganti review visual QA.
