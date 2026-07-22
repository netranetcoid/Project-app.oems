# Inbound Attendance Events AppBill → AppOEMS v1.0

Endpoint ini hanya menerima perubahan attendance dari AppBill. Employee, payroll,
KPI, dan overtime tidak dapat diubah melalui endpoint ini.

## Endpoint dan keamanan

`POST /api/v1/integrations/appbill/attendance-events`

Header wajib: Bearer token, `X-Company-Code: OEMS`, UUID v4 `X-Request-Id`,
`X-Timestamp` ISO-8601 UTC, nonce unik 16–191 karakter, `X-Signature-Version: 2`,
`X-Signature: sha256=<lowercase hex>`, `X-Event-ID`, dan `Idempotency-Key`.

Canonical HMAC-SHA256:

```text
timestamp + "\n" + nonce + "\n" + request_id + "\n" +
HTTP_METHOD_UPPERCASE + "\n" + PATH_ONLY + "\n" + RAW_BODY
```

Query string tidak ikut path dan raw body tidak di-encode ulang sebelum verifikasi.
Clock skew maksimum lima menit; nonce hanya dapat dipakai sekali.

Test vector:

```text
timestamp  = 2026-07-22T05:00:00+00:00
nonce      = 12345678-1234-4234-8234-123456789012
request_id = aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa
method     = POST
path       = /api/v1/integrations/appbill/attendance-events
raw_body   = {"schema_version":"1.0"}
secret     = secret
signature  = sha256=HMAC_SHA256_HEX(canonical, secret)
```

## Event dan payload

Event yang diterima: `attendance.corrected`, `attendance.approved`, dan
`attendance.cancelled`. Envelope wajib membawa schema `1.0`, UUID event ID,
idempotency key, company code, occurred-at dengan offset, source `appbill`, dan
object `data`.

Data wajib: `source_record_id`, integer `version`, `status`, `approval_status`,
boolean `is_cancelled`, `changed_by`, dan `updated_at`. `check_in`, `check_out`,
`shift_code`, dan `change_reason` nullable. Check-out tidak boleh mendahului
check-in. Cancellation selalu menjadi soft cancel dengan approval `rejected`.

Status attendance: `present`, `late`, `absent`, `leave`, `sick`, `permission`,
`holiday`, `off`, `incomplete`. Approval: `draft`, `submitted`, `approved`,
`rejected`, `corrected`.

## Idempotency, versioning, dan anti-echo

Hash payload adalah SHA-256 atas raw body. Event ID/idempotency key/revision yang
sama dengan hash sama menghasilkan `DUPLICATE`; hash berbeda menghasilkan 409.
Version lebih kecil menghasilkan `STALE`; version lebih besar diproses di dalam
transaction dengan row locking. Lookup attendance selalu dibatasi company.

Update memakai event suppression sehingga observer AppOEMS tidak membuat webhook
balik. Metadata source system, external event, revision, actor, dan waktu disimpan
untuk audit integrasi.

## Response dan error

Sukses HTTP 202 berisi `status` (`ACCEPTED`, `DUPLICATE`, atau `STALE`), `sync_id`,
dan `duplicate`. Konflik menggunakan 409; autentikasi/timestamp 401; company tidak
diizinkan 403; record tidak ditemukan 404; validasi 422; throttle 429. Response
tidak memuat credential, raw Authorization, SQL, atau stack trace.
