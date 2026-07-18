# Uji Koneksi Langsung AppOEMS -> AppBill v1.0

Tujuan endpoint ini hanya memverifikasi jaringan HTTPS, Bearer token, HMAC, dan
mapping company sebelum cutover. Endpoint **tidak boleh** membuat jurnal,
payroll, absensi, invoice, atau perubahan master data.

## Endpoint AppBill

`POST /api/v1/integrations/appoems/connection-test`

AppOEMS menyediakan path ini sebagai nilai default yang dapat diedit owner di
System > Integrasi, Audit & Health. Endpoint hanya dapat dipanggil dari tombol
**Uji Koneksi Live Sekarang**, secara sinkron; tidak memakai Laravel queue,
scheduler, ataupun tabel outbox.

## Header wajib

- `Authorization: Bearer <api_token>`
- `X-Company-Code: OEMS`
- `X-Request-Id: <uuid>`
- `X-Event-ID: <uuid>`
- `Idempotency-Key: appbill:direct-connection-test:<uuid>`
- `X-Signature: sha256=<HMAC-SHA256 raw request body>`

`OEMS` adalah company code teknis PT Ovall Solusindo Mandiri saat ini. Jangan
menggantinya ke `OSM` tanpa cutover terkoordinasi pada OEMS, APK, dan AppBill.

## Payload

```json
{
  "schema_version": "1.0",
  "event": "system.connection.test",
  "event_id": "uuid",
  "company_code": "OEMS",
  "occurred_at": "2026-07-18T10:00:00+07:00",
  "source": "appoems"
}
```

## Respons AppBill yang diharapkan

HTTP `200` atau `204`. Untuk JSON, format yang direkomendasikan:

```json
{
  "success": true,
  "data": { "status": "CONNECTED" }
}
```

Jika token/HMAC tidak valid, AppBill wajib memberi `401` atau `403` dan tidak
boleh memproses payload sebagai transaksi keuangan.

## Kebijakan kredensial OEMS

Token dan HMAC dibuat satu kali oleh akun bertanda **Developer** melalui
AppOEMS. Setelah pasangan lengkap dibuat, AppOEMS mengunci keduanya: form tidak
dapat mengubah nilai dan tombol rotasi tidak lagi tersedia. Nilai tetap
tersimpan dengan encrypted cast di database dan hanya dapat dibuka oleh akun
Developer setelah memasukkan ulang password Developer. Owner dapat menyetujui
live/cutover, tetapi tidak dapat melihat rahasia. Bila nilai benar-benar hilang
atau diduga bocor, perubahan hanya dilakukan melalui prosedur darurat yang
diaudit, serta harus diperbarui serentak di AppBill sebelum koneksi live
diaktifkan.
