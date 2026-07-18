<?php

namespace App\Services\Integration;

use App\Models\Company;
use App\Models\IntegrationConnection;
use App\Models\IntegrationOutbox;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AppBillTransport
{
    /**
     * Sends one already-encrypted outbox event. Retry is deliberately handled
     * by the outbox scheduler, never by an unbounded HTTP retry loop.
     */
    public function deliver(IntegrationConnection $connection, IntegrationOutbox $event): array
    {
        $credentials = $connection->credentials ?? [];
        $settings = $connection->settings ?? [];
        $baseUrl = rtrim((string) $connection->base_url, '/');
        $token = (string) ($credentials['api_token'] ?? '');
        $secret = (string) ($credentials['hmac_secret'] ?? '');
        if ($baseUrl === '' || $token === '' || $secret === '') {
            throw new RuntimeException('Konfigurasi live AppBill belum lengkap.');
        }
        if (parse_url($baseUrl, PHP_URL_SCHEME) !== 'https' && app()->environment('production')) {
            throw new RuntimeException('Endpoint production AppBill wajib HTTPS.');
        }

        $company = Company::query()->findOrFail($event->company_id);
        $timezone = in_array((string) $company->timezone, timezone_identifiers_list(), true)
            ? (string) $company->timezone
            : 'Asia/Jakarta';
        $isAttendance = Str::startsWith($event->event_type, ['attendance.', 'employee.']);
        $path = $isAttendance
            ? (string) ($settings['attendance_webhook_path'] ?? '/api/integrations/attendance/webhook')
            : (string) ($settings['payroll_endpoint_path'] ?? '/api/v1/integrations/appoems/payroll-periods');
        $payload = $isAttendance
            ? [
                // Schema API tidak sama dengan version revisi record absensi.
                'schema_version' => '1.0',
                'event' => $event->event_type,
                'event_id' => $event->event_id,
                // Event timestamp memakai timezone bisnis yang sama dengan
                // data absensi, sedangkan storage internal boleh tetap UTC.
                'occurred_at' => $event->created_at?->copy()->setTimezone($timezone)->toIso8601String(),
                'company_code' => $company->code,
                'data' => $event->payload,
            ]
            : array_merge($event->payload, [
                'event_id' => $event->event_id,
                'legal_entity_code' => $event->payload['legal_entity_code'] ?? $company->code,
            ]);
        $raw = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $requestId = (string) Str::uuid();

        $response = Http::acceptJson()
            ->asJson()
            ->withToken($token)
            ->withHeaders([
                'X-Company-Code' => $company->code,
                'X-Request-Id' => $requestId,
                'X-Event-ID' => $event->event_id,
                'Idempotency-Key' => $event->idempotency_key,
                'X-Signature' => 'sha256=' . hash_hmac('sha256', $raw, $secret),
            ])
            ->timeout(max(1, min(60, (int) $connection->timeout_seconds)))
            ->withBody($raw, 'application/json')
            ->post($baseUrl . '/' . ltrim($path, '/'));

        if (! $response->successful()) {
            throw new RuntimeException("AppBill HTTP {$response->status()}: " . Str::limit((string) $response->body(), 300, ''));
        }

        $body = $response->json();
        if (is_array($body) && array_key_exists('success', $body) && $body['success'] === false) {
            throw new RuntimeException('AppBill menolak event: ' . Str::limit((string) ($body['message'] ?? 'unknown'), 300, ''));
        }

        return [
            'status' => $response->status(),
            'summary' => [
                'code' => data_get($body, 'data.status', data_get($body, 'status', 'ACCEPTED')),
                'receipt_id' => data_get($body, 'data.sync_id', data_get($body, 'data.external_receipt_id')),
                'request_id' => $requestId,
            ],
        ];
    }

    /**
     * Tests the live AppBill channel without creating an outbox event.
     *
     * This is intentionally limited to a signed technical handshake. Payroll,
     * attendance, employee, selfie, GPS, and other operational data must only
     * be delivered through the audited outbox after the owner approves cutover.
     */
    public function testLiveConnection(IntegrationConnection $connection): array
    {
        $credentials = $connection->credentials ?? [];
        $settings = $connection->settings ?? [];
        $baseUrl = rtrim((string) $connection->base_url, '/');
        $token = (string) ($credentials['api_token'] ?? '');
        $secret = (string) ($credentials['hmac_secret'] ?? '');

        if ($connection->mode !== 'live' || ! $connection->is_enabled) {
            throw new RuntimeException('Koneksi AppBill belum berada pada mode live aktif.');
        }
        if ($baseUrl === '' || $token === '' || $secret === '') {
            throw new RuntimeException('Konfigurasi live AppBill belum lengkap.');
        }
        if (parse_url($baseUrl, PHP_URL_SCHEME) !== 'https') {
            throw new RuntimeException('Uji koneksi live hanya diizinkan melalui HTTPS.');
        }

        $company = Company::query()->findOrFail($connection->company_id);
        $timezone = in_array((string) $company->timezone, timezone_identifiers_list(), true)
            ? (string) $company->timezone
            : 'Asia/Jakarta';
        $eventId = (string) Str::uuid();
        $requestId = (string) Str::uuid();
        $payload = [
            'schema_version' => '1.0',
            'event' => 'system.connection.test',
            'event_id' => $eventId,
            'company_code' => $company->code,
            'occurred_at' => now()->setTimezone($timezone)->toIso8601String(),
            'source' => 'appoems',
        ];
        $raw = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $path = (string) ($settings['connection_test_path'] ?? '/api/v1/integrations/appoems/connection-test');

        $request = Http::acceptJson()
            ->asJson()
            ->withToken($token)
            ->withHeaders([
                'X-Company-Code' => $company->code,
                'X-Request-Id' => $requestId,
                'X-Event-ID' => $eventId,
                'Idempotency-Key' => "appbill:direct-connection-test:{$eventId}",
                'X-Signature' => 'sha256=' . hash_hmac('sha256', $raw, $secret),
            ])
            ->timeout(max(1, min(60, (int) $connection->timeout_seconds)));

        // TLS verification stays on by default; owner may only disable it for
        // an explicitly approved private staging environment.
        if (! $connection->verify_tls) {
            $request = $request->withoutVerifying();
        }

        $response = $request->withBody($raw, 'application/json')
            ->post($baseUrl . '/' . ltrim($path, '/'));

        if (! $response->successful()) {
            throw new RuntimeException("Uji AppBill HTTP {$response->status()}: " . Str::limit((string) $response->body(), 300, ''));
        }

        $body = $response->json();
        if (is_array($body) && array_key_exists('success', $body) && $body['success'] === false) {
            throw new RuntimeException('AppBill menolak uji koneksi: ' . Str::limit((string) ($body['message'] ?? 'unknown'), 300, ''));
        }

        return [
            'status' => $response->status(),
            'summary' => [
                'code' => data_get($body, 'data.status', data_get($body, 'status', 'CONNECTED')),
                'request_id' => $requestId,
                'event_id' => $eventId,
            ],
        ];
    }
}
