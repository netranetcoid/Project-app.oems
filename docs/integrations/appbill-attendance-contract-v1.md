# Kontrak Absensi AppOEMS → AppBill v1.0

Status: **siap untuk normalisasi AppBill**, setelah koneksi LIVE, token, HMAC, dan cutover owner diaktifkan. Referensi ini berlaku untuk legal entity PT OSM dan produk/site yang berada di bawahnya.

Base URL OEMS: `https://oems.osm.net.id/api/v1/integrations/appbill`.

## Aturan umum

- Semua endpoint memakai HTTPS, `Authorization: Bearer <token>`, `X-Company-Code`, `X-Request-Id`, dan `X-Signature: sha256=<HMAC-SHA256 raw body>`.
- `source_record_id` adalah identitas absensi permanen dari OEMS. AppBill wajib menyimpan dan mengunci unik per `company_code + source_record_id`.
- `version` adalah **versi revisi record**, integer mulai dari `1`. Event dengan `version` lebih kecil atau sama dari record yang telah diproses harus dianggap duplikat/stale.
- `schema_version` adalah versi kontrak API (`"1.0"`), bukan versi record.
- Bukti selfie, GPS, foto, koordinat, token, dan data rekening tidak pernah dikirim ke AppBill.

## Timezone

- Timezone canonical adalah nilai `companies.timezone`; default PT OSM: `Asia/Jakarta` (`UTC+07:00`).
- `attendance_date` adalah tanggal kalender **lokal perusahaan**, format `YYYY-MM-DD`.
- `check_in`, `check_out`, `updated_at`, dan `occurred_at` adalah ISO-8601/RFC3339 dengan offset. Contoh: `2026-07-18T08:15:00+07:00`.
- OEMS boleh menyimpan timestamp secara UTC di database, namun mengonversi keluar ke timezone perusahaan sebelum mengirim API/webhook.

## GET `/employees`

Query: `per_page` opsional, `1..100`, default `100`.

```json
{
  "success": true,
  "data": [
    {
      "employee_code": "OSM-EMP-001",
      "name": "Budi Santoso",
      "email": "budi@osm.net.id",
      "phone": "081234567890",
      "division": "Teknis",
      "division_type": "operational",
      "employment_status": "active",
      "updated_at": "2026-07-18T08:00:00+07:00"
    }
  ],
  "meta": { "current_page": 1, "per_page": 100, "total": 1, "last_page": 1, "schema_version": "1.0", "timezone": "Asia/Jakarta" }
}
```

| Field | Wajib | Nullable | Keterangan |
|---|---:|---:|---|
| `employee_code` | Ya | Tidak | `employees.employee_no`; kunci employee lintas sistem. |
| `name` | Ya | Tidak | Nama karyawan. |
| `email`, `phone`, `division`, `division_type` | Tidak | Ya | Informasi master, tidak menjadi kunci normalisasi. |
| `employment_status` | Ya | Tidak | Status kerja OEMS, mis. `active`, `inactive`, `suspended`, `resigned`, `terminated`. |
| `updated_at` | Ya | Tidak | Timestamp perubahan master employee. |

## GET `/shifts`

Query: `per_page` opsional, `1..100`, default `100`. Hanya shift aktif milik perusahaan pada header yang dikirim.

```json
{
  "success": true,
  "data": [
    {
      "shift_code": "REG-0800",
      "shift_name": "Reguler Pagi",
      "start_time": "08:00",
      "end_time": "17:00",
      "break_minutes": 60,
      "timezone": "Asia/Jakarta"
    }
  ],
  "meta": { "current_page": 1, "per_page": 100, "total": 1, "last_page": 1, "schema_version": "1.0", "timezone": "Asia/Jakarta" }
}
```

| Field | Wajib | Nullable | Keterangan |
|---|---:|---:|---|
| `shift_code`, `shift_name`, `start_time`, `end_time`, `break_minutes`, `timezone` | Ya | Tidak | `start_time`/`end_time` format `HH:mm`; shift lintas tengah malam tetap memakai tanggal lokal record absensi. |

## GET `/attendance`

Query wajib: `start_date=YYYY-MM-DD` dan `end_date=YYYY-MM-DD`; rentang menggunakan timezone perusahaan. `per_page` opsional `1..100`.

```json
{
  "success": true,
  "data": [
    {
      "source_record_id": "ATT-4F20A33E-7C43-4AA4-8F67-7F4FE7B951B9",
      "employee_code": "OSM-EMP-001",
      "attendance_date": "2026-07-18",
      "check_in": "2026-07-18T08:12:16+07:00",
      "check_out": "2026-07-18T17:05:41+07:00",
      "status": "late",
      "late_minutes": 12,
      "work_minutes": 533,
      "shift_code": "REG-0800",
      "timezone": "Asia/Jakarta",
      "approval_status": "approved",
      "version": 2,
      "updated_at": "2026-07-18T17:06:02+07:00",
      "is_cancelled": false,
      "change_reason": null
    }
  ],
  "meta": { "current_page": 1, "per_page": 100, "total": 1, "last_page": 1, "schema_version": "1.0", "timezone": "Asia/Jakarta" }
}
```

`GET /attendance/{source_record_id}` mengembalikan satu object `data` dengan schema absensi yang sama.

| Field | Wajib | Nullable | Keterangan |
|---|---:|---:|---|
| `source_record_id` | Ya | Tidak | ID permanen OEMS; tidak berubah walau record dikoreksi. |
| `employee_code`, `attendance_date`, `status`, `timezone`, `approval_status`, `version`, `updated_at`, `is_cancelled` | Ya | Tidak | Field kanonik normalisasi. |
| `check_in`, `check_out` | Tidak | Ya | `check_out=null` saat belum pulang; keduanya dapat null untuk status non-hadir. |
| `late_minutes`, `work_minutes` | Ya | Tidak | Integer menit, `0` jika belum dapat dihitung. |
| `shift_code` | Tidak | Ya | Null jika karyawan belum mendapat shift. |
| `change_reason` | Tidak | Ya | Alasan koreksi/pembatalan jika tersedia. |

## Webhook `attendance.created`

Webhook dikirim OEMS ke endpoint AppBill yang dikonfigurasi owner, default `/api/integrations/attendance/webhook`. Header penting: `X-Event-ID` dan `Idempotency-Key`; keduanya harus disimpan oleh AppBill.

```json
{
  "schema_version": "1.0",
  "event": "attendance.created",
  "event_id": "8dd96b52-46ce-4f91-99de-bcbd46e92f44",
  "occurred_at": "2026-07-18T08:12:20+07:00",
  "company_code": "OSM",
  "data": {
    "source_record_id": "ATT-4F20A33E-7C43-4AA4-8F67-7F4FE7B951B9",
    "employee_code": "OSM-EMP-001",
    "attendance_date": "2026-07-18",
    "check_in": "2026-07-18T08:12:16+07:00",
    "check_out": null,
    "status": "late",
    "late_minutes": 12,
    "work_minutes": 0,
    "shift_code": "REG-0800",
    "timezone": "Asia/Jakarta",
    "approval_status": "approved",
    "version": 1,
    "updated_at": "2026-07-18T08:12:16+07:00",
    "is_cancelled": false,
    "change_reason": null
  }
}
```

## Webhook `attendance.updated`

Schema sama dengan event created. Event ini dikirim saat check-out, perubahan status/shift, approval, koreksi, atau pembatalan. `version` selalu lebih besar dari revisi sebelumnya.

```json
{
  "schema_version": "1.0",
  "event": "attendance.updated",
  "event_id": "3c06c11a-2f21-49cd-a398-12b7c2b9889c",
  "occurred_at": "2026-07-18T17:06:02+07:00",
  "company_code": "OSM",
  "data": {
    "source_record_id": "ATT-4F20A33E-7C43-4AA4-8F67-7F4FE7B951B9",
    "employee_code": "OSM-EMP-001",
    "attendance_date": "2026-07-18",
    "check_in": "2026-07-18T08:12:16+07:00",
    "check_out": "2026-07-18T17:05:41+07:00",
    "status": "late",
    "late_minutes": 12,
    "work_minutes": 533,
    "shift_code": "REG-0800",
    "timezone": "Asia/Jakarta",
    "approval_status": "approved",
    "version": 2,
    "updated_at": "2026-07-18T17:06:02+07:00",
    "is_cancelled": false,
    "change_reason": "Check-out karyawan tercatat."
  }
}
```

## Enum absensi dan approval

| `status` | Arti untuk AppBill |
|---|---|
| `present` | Hadir tepat waktu. |
| `late` | Hadir, tetapi terlambat; gunakan `late_minutes`. |
| `absent` | Tidak hadir tanpa status izin/cuti/sakit. |
| `leave` | Cuti disetujui. |
| `sick` | Sakit. |
| `permission` | Izin. |
| `holiday` | Hari libur perusahaan/nasional. |
| `off` | Hari off jadwal/roster. |
| `incomplete` | Data tidak lengkap atau status source di luar enum kanonik; jangan dihitung sebagai hadir penuh tanpa aturan bisnis AppBill. |

| `approval_status` | Arti |
|---|---|
| `draft`, `submitted` | Belum final; jangan finalisasi dampak payroll. |
| `approved` | Siap dipakai perhitungan sesuai cutover. |
| `rejected` | Ditolak; jangan dihitung sebagai data hadir yang disetujui. |
| `corrected` | Telah dikoreksi; pakai `version` terbaru. |

## Kontrak machine-readable dan cutover

Setelah koneksi AppBill LIVE, AppBill dapat membaca kontrak dari `GET /attendance-contract` menggunakan autentikasi HMAC yang sama. Sebelum LIVE, gunakan dokumen ini sebagai acuan implementasi.

Normalisasi baru boleh diaktifkan setelah AppBill berhasil melakukan backfill `GET /employees`, `GET /shifts`, dan `GET /attendance`, lalu uji idempotensi created/updated. Cutover tanggal dan batas delegasi tetap mengikuti konfigurasi owner di AppOEMS.
