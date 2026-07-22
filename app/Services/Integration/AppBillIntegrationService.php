<?php

namespace App\Services\Integration;

use App\Models\IntegrationConnection;
use App\Models\IntegrationOutbox;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AppBillIntegrationService
{
    public function __construct(private AppBillTransport $transport) {}

    public function connection(int $companyId): IntegrationConnection
    {
        return IntegrationConnection::firstOrCreate(
            ['company_id' => $companyId, 'provider' => 'appbill'],
            [
                'name' => 'AppBill',
                'mode' => 'mock',
                'auth_type' => 'none',
                'is_enabled' => true,
                'allow_outbound' => true,
                'allow_inbound' => true,
                'health_status' => 'ready',
                // Payroll biasa tetap sinkron. Detail BPJS bersifat opsional
                // sampai mapping akun BPJS di AppBill disetujui.
                'settings' => [
                    'dummy_only' => true,
                    'live_activation_confirmed' => false,
                    'bpjs_payload_enabled' => false,
                    'company_code' => 'OEMS',
                    'connection_test_path' => '/api/v1/integrations/appoems/connection-test',
                    'attendance_webhook_path' => '/api/integrations/attendance/webhook',
                    'payroll_endpoint_path' => '/api/v1/integrations/appoems/payroll-periods',
                ],
            ]
        );
    }

    public function queueEvent(
        int $companyId,
        string $eventType,
        array $payload,
        string $idempotencyKey,
        ?string $aggregateType = null,
        string|int|null $aggregateId = null
    ): IntegrationOutbox {
        $connection = $this->connection($companyId);

        return IntegrationOutbox::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'event_id' => (string) Str::uuid(),
                'company_id' => $companyId,
                'integration_connection_id' => $connection->id,
                'event_type' => $eventType,
                'aggregate_type' => $aggregateType,
                'aggregate_id' => $aggregateId,
                'payload' => $payload,
                'status' => 'pending',
                'next_retry_at' => now(),
            ]
        );
    }

    public function queuePayrollPeriod(PayrollPeriod $period): ?IntegrationOutbox
    {
        if (! Schema::hasTable('integration_outbox')) {
            return null;
        }

        $period->loadMissing(['slips.items']);
        $includeBpjs = $this->bpjsPayloadEnabled((int) $period->company_id);
        $payload = [
            'schema_version' => '1.0',
            'source' => 'appoems',
            'event' => 'payroll.period.published',
            'company_id' => (int) $period->company_id,
            'period' => sprintf('%04d-%02d', $period->period_year, $period->period_month),
            'salary_payment_date' => $period->salary_payment_date?->toDateString(),
            'kpi_payment_date' => $period->kpi_payment_date?->toDateString(),
            'totals' => [
                'gross' => (string) $period->total_gross,
                'deduction' => (string) $period->total_deduction,
                'net' => (string) $period->total_net,
                'kpi_bonus' => (string) $period->total_kpi_bonus,
                'employee_count' => $period->slips->count(),
            ],
            // Detail payroll terenkripsi di outbox. Transport live tetap
            // terkunci sampai kontrak API AppBill disetujui owner.
            'slips' => $period->slips->map(fn ($slip): array => [
                'slip_no' => $slip->slip_no,
                'employee_no' => $slip->employee_no_snapshot,
                'employee_name' => $slip->employee_name_snapshot,
                'branch' => $slip->branch_name_snapshot,
                'position' => $slip->position_name_snapshot,
                'bank_name' => $slip->bank_name_snapshot,
                'bank_account' => $slip->bank_account_snapshot,
                'gross_income' => (string) $slip->gross_income,
                'total_deduction' => (string) $slip->total_deduction,
                'net_pay' => (string) $slip->net_pay,
                'kpi_bonus' => (string) $slip->kpi_bonus,
                'items' => $slip->items->map(fn ($item): array => [
                    'category' => $item->category,
                    'code' => $item->code,
                    'name' => $item->name,
                    'amount' => (string) $item->amount,
                ])->values()->all(),
            ])->values()->all(),
        ];

        if (! $includeBpjs) {
            // Item BPJS juga disaring agar AppBill benar-benar hanya menerima
            // payroll inti, bukan rincian yang terselip di daftar komponen.
            $payload = $this->withoutBpjsPayload($payload);
        }

        // AppBill tetap menerima payroll inti. Rincian BPJS baru dilampirkan
        // bila toggle khusus diaktifkan; tidak ada switch yang mematikan gaji.
        if ($includeBpjs) {
            $payload['totals']['company_burden'] = (string) $period->total_company_burden;
            $payload['slips'] = $period->slips->map(fn ($slip): array => [
                'slip_no' => $slip->slip_no,
                'employee_no' => $slip->employee_no_snapshot,
                'employee_name' => $slip->employee_name_snapshot,
                'branch' => $slip->branch_name_snapshot,
                'position' => $slip->position_name_snapshot,
                'bank_name' => $slip->bank_name_snapshot,
                'bank_account' => $slip->bank_account_snapshot,
                'gross_income' => (string) $slip->gross_income,
                'total_deduction' => (string) $slip->total_deduction,
                'net_pay' => (string) $slip->net_pay,
                'bpjs' => $this->bpjsPayloadForSlip($slip),
                'kpi_bonus' => (string) $slip->kpi_bonus,
                'items' => $slip->items->map(fn ($item): array => [
                    'category' => $item->category, 'code' => $item->code,
                    'name' => $item->name, 'amount' => (string) $item->amount,
                ])->values()->all(),
            ])->values()->all();
        }

        return $this->queueEvent(
            (int) $period->company_id,
            'payroll.period.published',
            $payload,
            "appbill:payroll-period:{$period->id}:published",
            PayrollPeriod::class,
            $period->id
        );
    }

    public function queueTestEvent(int $companyId, int $userId): IntegrationOutbox
    {
        $nonce = (string) Str::uuid();

        return $this->queueEvent(
            $companyId,
            'system.connection.test',
            [
                'schema_version' => '1.0',
                'source' => 'appoems',
                'requested_by' => $userId,
                'requested_at' => now()->toIso8601String(),
                'nonce' => $nonce,
            ],
            "appbill:connection-test:$companyId:$nonce",
            'system',
            $companyId
        );
    }

    /**
     * Owner-triggered, synchronous connectivity check. Unlike dispatch(), this
     * never creates an integration_outbox row and never transfers HR data.
     */
    public function testLiveConnection(IntegrationConnection $connection): array
    {
        return $this->transport->testLiveConnection($connection);
    }

    public function dispatch(IntegrationOutbox $event): IntegrationOutbox
    {
        if ($event->status === 'sent') {
            return $event;
        }

        $event = DB::transaction(function () use ($event): IntegrationOutbox {
            $locked = IntegrationOutbox::query()->lockForUpdate()->findOrFail($event->id);
            if ($locked->status === 'sent') {
                return $locked;
            }
            $locked->update([
                'status' => 'processing',
                'attempts' => (int) $locked->attempts + 1,
                'locked_at' => now(),
                'last_attempt_at' => now(),
                'last_error' => null,
                'retry_category' => null,
                'retry_after_seconds' => null,
                'last_error_code' => null,
            ]);

            return $locked->fresh('connection');
        });

        $connection = $event->connection;

        try {
            // Toggle BPJS tidak pernah menghentikan payroll normal. Jika
            // dimatikan setelah event dibuat, payload lama disanitasi dahulu.
            if ($event->event_type === 'payroll.period.published' && ! $this->bpjsPayloadEnabledForConnection($connection)) {
                $event->update(['payload' => $this->withoutBpjsPayload($event->payload ?? [])]);
            }

            if (! $connection->is_enabled || ! $connection->allow_outbound) {
                throw new RuntimeException('Koneksi outbound AppBill sedang dinonaktifkan.');
            }

            if ($connection->mode === 'mock') {
                $delivery = [
                    'status' => 202,
                    'summary' => [
                        'code' => 'MOCK_ACCEPTED',
                        'message' => 'Dummy AppBill menerima event tanpa koneksi jaringan.',
                    ],
                ];
            } elseif ($connection->mode === 'live') {
                // Transport baru boleh aktif melalui konfigurasi owner dengan
                // HTTPS, token, HMAC, URL, dan tanggal cutover yang lengkap.
                $delivery = $this->transport->deliver($connection, $event);
            } else {
                throw new RuntimeException('Mode koneksi AppBill tidak dikenal.');
            }

            $event->update([
                'status' => 'sent',
                'sent_at' => now(),
                'locked_at' => null,
                'next_retry_at' => null,
                'response_status' => $delivery['status'],
                'response_summary' => $delivery['summary'],
                'retry_category' => null,
                'retry_after_seconds' => null,
                'last_error_code' => null,
            ]);
            $connection->update(['health_status' => 'ready', 'last_success_at' => now()]);
        } catch (Throwable $exception) {
            $attempts = (int) $event->attempts;
            $retryLimit = max(1, (int) $connection->retry_limit);
            $failure = $this->classifyFailure($exception);
            $isDead = $failure['permanent'] || $attempts >= $retryLimit;
            $delaySeconds = $failure['retry_after'] ?? min(3600, 30 * (2 ** max(0, $attempts - 1)));

            $event->update([
                'status' => $isDead ? 'dead' : 'failed',
                'locked_at' => null,
                'next_retry_at' => $isDead ? null : now()->addSeconds($delaySeconds),
                'last_error' => Str::limit($exception->getMessage(), 1000, ''),
                'retry_category' => $failure['category'],
                'retry_after_seconds' => $isDead ? null : $delaySeconds,
                'last_error_code' => $failure['code'],
            ]);
            $connection->update(['health_status' => 'warning', 'last_failure_at' => now()]);
        }

        return $event->fresh('connection');
    }

    public function dispatchPending(int $companyId, int $limit = 50): array
    {
        $events = IntegrationOutbox::forCompany($companyId)
            ->whereIn('status', ['pending', 'failed'])
            ->where(fn ($query) => $query->whereNull('next_retry_at')->orWhere('next_retry_at', '<=', now()))
            ->oldest('id')
            ->limit(max(1, min($limit, 100)))
            ->get();

        $result = ['processed' => 0, 'sent' => 0, 'failed' => 0, 'dead' => 0];
        foreach ($events as $event) {
            $updated = $this->dispatch($event);
            $result['processed']++;
            if (isset($result[$updated->status])) {
                $result[$updated->status]++;
            }
        }

        return $result;
    }

    public function retry(IntegrationOutbox $event): IntegrationOutbox
    {
        if ($event->status === 'sent') {
            return $event;
        }
        $event->update([
            'status' => 'pending',
            'attempts' => 0,
            'next_retry_at' => now(),
            'locked_at' => null,
            'last_error' => null,
            'retry_category' => null,
            'retry_after_seconds' => null,
            'last_error_code' => null,
        ]);

        return $event->fresh();
    }

    /** Status khusus detail BPJS; pengiriman payroll inti tidak bergantung padanya. */
    public function bpjsPayloadEnabled(int $companyId): bool
    {
        return $this->bpjsPayloadEnabledForConnection($this->connection($companyId));
    }

    /** Menghapus rincian BPJS dari antrean payroll yang belum terkirim, bukan membatalkan gaji. */
    public function redactPendingPayrollBpjs(IntegrationConnection $connection): int
    {
        $events = IntegrationOutbox::query()
            ->where('integration_connection_id', $connection->id)
            ->where('event_type', 'payroll.period.published')
            ->whereIn('status', ['pending', 'failed'])
            ->get();

        foreach ($events as $event) {
            $event->update(['payload' => $this->withoutBpjsPayload($event->payload ?? [])]);
        }

        return $events->count();
    }

    private function bpjsPayloadEnabledForConnection(IntegrationConnection $connection): bool
    {
        return (bool) data_get($connection->settings, 'bpjs_payload_enabled', false);
    }

    /** Retry hanya untuk gangguan sementara; 4xx konfigurasi tidak diputar tanpa akhir. */
    private function classifyFailure(Throwable $exception): array
    {
        $message = $exception->getMessage();
        $status = $exception instanceof AppBillTransportException ? $exception->httpStatus : null;
        if ($status === null) {
            preg_match('/HTTP\s+(\d{3})/', $message, $match);
            $status = isset($match[1]) ? (int) $match[1] : null;
        }
        $retryAfter = $exception instanceof AppBillTransportException ? $exception->retryAfterSeconds : null;
        if ($status === 429) {
            return ['category' => 'rate_limited', 'code' => 'HTTP_429', 'permanent' => false, 'retry_after' => $retryAfter ?? 300];
        }
        if (in_array($status, [408, 425, 500, 502, 503, 504], true)) {
            return ['category' => 'server_or_timeout', 'code' => 'HTTP_'.$status, 'permanent' => false, 'retry_after' => $retryAfter];
        }
        if ($status !== null && $status >= 400) {
            return ['category' => 'configuration_or_validation', 'code' => 'HTTP_'.$status, 'permanent' => true, 'retry_after' => null];
        }
        if (str_contains(strtolower($message), 'konfigurasi')) {
            return ['category' => 'configuration', 'code' => 'CONFIG', 'permanent' => true, 'retry_after' => null];
        }

        return ['category' => 'network_or_unknown', 'code' => 'TRANSPORT', 'permanent' => false, 'retry_after' => null];
    }

    private function bpjsPayloadForSlip($slip): array
    {
        return [
            'wage_base' => (string) $slip->bpjs_wage_base,
            'health_company' => (string) $slip->bpjs_kesehatan_perusahaan,
            'health_employee' => (string) $slip->bpjs_kesehatan_karyawan,
            'jht_company' => (string) $slip->jht_perusahaan,
            'jht_employee' => (string) $slip->jht_karyawan,
            'jp_company' => (string) $slip->jp_perusahaan,
            'jp_employee' => (string) $slip->jp_karyawan,
            'jkk_company' => (string) $slip->jkk,
            'jkm_company' => (string) $slip->jkm,
            'total_company_burden' => (string) $slip->total_company_burden,
        ];
    }

    private function withoutBpjsPayload(array $payload): array
    {
        unset($payload['totals']['company_burden']);
        foreach ($payload['slips'] ?? [] as $index => $slip) {
            unset($slip['bpjs']);
            $slip['items'] = array_values(array_filter(
                $slip['items'] ?? [],
                fn (array $item): bool => ! in_array($item['code'] ?? null, [
                    'bpjs_kesehatan_perusahaan', 'bpjs_kesehatan_karyawan',
                    'jht_perusahaan', 'jht_karyawan', 'jp_perusahaan',
                    'jp_karyawan', 'jkk', 'jkm',
                ], true)
            ));
            $payload['slips'][$index] = $slip;
        }

        return $payload;
    }
}
