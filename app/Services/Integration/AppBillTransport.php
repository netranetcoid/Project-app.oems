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
}
