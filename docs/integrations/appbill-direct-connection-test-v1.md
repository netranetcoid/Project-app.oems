# Uji Koneksi Langsung AppOEMS → AppBill v1.0

Uji ini sinkron, tidak memakai outbox, dan tidak mengirim employee, attendance,
payroll, selfie, GPS, atau data HR lain. Uji tidak mengaktifkan cutover.

## Konfigurasi

- Base URL disimpan terpisah, tanpa akhiran `/api`, misalnya `https://appbill-domain.test`.
- Company code default: `OEMS`.
- Path: `/api/v1/integrations/appoems/connection-test`.
- Production wajib HTTPS dan TLS verification aktif.

## Request

`POST /api/v1/integrations/appoems/connection-test`

```json
{"schema_version":"1.0","event":"system.connection.test","event_id":"uuid","company_code":"OEMS","occurred_at":"ISO-8601","source":"appoems"}
```

Header wajib: `Authorization: Bearer …`, `X-Company-Code: OEMS`, UUID v4
`X-Request-Id`, timestamp UTC `X-Timestamp`, nonce unik `X-Nonce`,
`X-Signature-Version: 2`, `X-Signature: sha256=<lowercase hex>`,
`X-Event-ID: <event_id>`, dan
`Idempotency-Key: appbill:direct-connection-test:<event_id>`.

Canonical HMAC-SHA256 harus persis:

```text
timestamp + "\n" + nonce + "\n" + request_id + "\n" +
HTTP_METHOD_UPPERCASE + "\n" + PATH_ONLY + "\n" + RAW_BODY
```

Query string tidak masuk canonical path. Raw body tidak boleh di-encode ulang
setelah signature dibuat. Sukses adalah HTTP 204, atau HTTP 200 dengan
`success=true` dan `data.status=CONNECTED`.

Monitoring hanya menyimpan waktu tes/sukses/gagal, health status, HTTP status,
request ID, durasi, dan kategori error aman. Token, HMAC secret, Authorization,
dan raw response sensitif tidak disimpan.
