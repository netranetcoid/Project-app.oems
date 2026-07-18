# Standar komentar kode AppOEMS & OvallHR

Tujuan komentar adalah membantu owner/developer memahami **alasan dan dampak**
kode, bukan mengulang baris yang sudah jelas dibaca.

## Aturan wajib untuk perubahan berikutnya

1. Controller, service, API endpoint, migration, seeder, dan job baru wajib
   punya docblock ringkas tentang peran dan batas tanggung jawabnya.
2. Bagian yang menyentuh approval, payroll, KPI, presensi, GPS/selfie, token,
   atau integrasi wajib menerangkan aturan bisnis serta risiko editnya.
3. Migration wajib menerangkan data yang dibuat dan alasan index penting.
4. Blade/Dart hanya diberi komentar pada blok yang berisi aturan penting atau
   keputusan UX; jangan memberi komentar pada setiap tag/widget sederhana.
5. Jangan taruh password, token, data pribadi, atau kredensial di komentar.
6. Saat perilaku diubah, komentar terkait harus diperbarui pada commit yang
   sama agar dokumentasi tidak menyesatkan.

## Pola singkat

```php
/** Mengapa proses ini ada dan data apa yang boleh diubah. */
public function approve(...) { /* aturan bisnis sensitif */ }
```

```dart
// Bukti lokasi diproses di perangkat sebelum upload agar foto asli tidak
// tertukar dengan file watermark; validasi akhir tetap berada di API.
```
