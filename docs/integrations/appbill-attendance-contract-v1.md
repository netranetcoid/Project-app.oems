# Kontrak Integrasi AppOEMS ↔ AppBill v1.0

AppOEMS adalah source of truth. AppBill beroperasi sebagai consumer/provider-only:
menarik employee, shift, dan attendance secara read-only serta menerima webhook
outbound. Tidak ada cutover otomatis.

## Endpoint AppBill → AppOEMS

- `GET /api/v1/integrations/appbill/attendance-contract`
- `GET /api/v1/integrations/appbill/employees`
- `GET /api/v1/integrations/appbill/shifts`
- `GET /api/v1/integrations/appbill/attendance`
- `GET /api/v1/integrations/appbill/attendance/{source_record_id}`

Route write attendance lama dapat tetap ada untuk backward compatibility, tetapi
bukan capability provider-only dan tidak boleh dipakai oleh provider AppBill.
Endpoint mobile bukan endpoint integrasi AppBill.

## Webhook AppOEMS → AppBill

- Employee dan attendance: `POST /api/integrations/attendance/webhook`
  (alias kompatibel: `/api/v1/integrations/appoems/attendance/webhook`).
- Payroll published: `POST /api/v1/integrations/appoems/payroll-periods`.
- Connection test: `POST /api/v1/integrations/appoems/connection-test`.

Event tersedia: `employee.created`, `employee.updated`, `attendance.created`,
`attendance.updated`, dan `payroll.period.published`. Overtime integration, KPI
integration, dan payroll pull tidak tersedia.

## HMAC v2

Semua request integrasi memakai Bearer token, company code `OEMS`, UUID v4 request
ID, timestamp ISO-8601 UTC, nonce unik 16–191 karakter, signature version `2`, dan
signature `sha256=<lowercase HMAC-SHA256 hex>`.

```text
timestamp + "\n" + nonce + "\n" + request_id + "\n" +
HTTP_METHOD_UPPERCASE + "\n" + PATH_ONLY + "\n" + RAW_BODY
```

Query string tidak ikut path. Clock skew maksimum lima menit. Nonce dan request
ID baru dibuat pada setiap HTTP attempt; event ID, business idempotency key, dan
raw business payload tetap stabil pada retry.

## Attendance

`source_record_id` permanen dan `version` monoton naik. Idempotency key adalah
`appbill:attendance:{source_record_id}:v{version}`. Check-out, koreksi, approval,
dan cancellation dikirim sebagai `attendance.updated`; versi lama tidak boleh
menggantikan revisi baru. Timestamp data memakai ISO-8601 dengan offset timezone
perusahaan. Status attendance dan approval mengikuti machine-readable contract.
Selfie, GPS, koordinat, bukti foto, dan device data tidak dikirim.

## Employee dan payroll

Employee tanpa employee code tidak dikirim. Payload hanya berisi kode, nama,
email, telepon, divisi, tipe divisi, status kerja, dan waktu update. Idempotency
berubah saat revisi employee berubah.

Payroll hanya dikirim setelah published dengan key
`appbill:payroll-period:{payroll_period_id}:published`. Duplicate publish tidak
membuat event baru. Payload outbox terenkripsi; toggle BPJS hanya mengatur detail
BPJS dan tidak menghentikan payroll inti. Webhook tidak mengubah status pembayaran,
jurnal, atau perhitungan payroll.

## Retry dan keamanan

Retry terbatas hanya untuk 408, 425, 429, 500, 502, 503, 504, timeout, dan network
failure; `Retry-After` dihormati. 400, 401, 403, 404, payload conflict 409, dan 422
bersifat permanen. Replay nonce ditolak 409; timestamp expired, token/signature
salah, atau connection/company tidak aktif ditolak 401; format header invalid
ditolak 422. Credential dan payload sensitif tidak dimasukkan ke response atau log.
