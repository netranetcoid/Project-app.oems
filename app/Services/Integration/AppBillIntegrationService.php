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
    public function __construct(private AppBillTransport $transport)
    {
    }

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
                'settings' => ['dummy_only' => true, 'live_activation_confirmed' => false],
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
                'last_error' => null,
            ]);
            return $locked->fresh('connection');
        });

        $connection = $event->connection;

        try {
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
            ]);
            $connection->update(['health_status' => 'ready', 'last_success_at' => now()]);
        } catch (Throwable $exception) {
            $attempts = (int) $event->attempts;
            $retryLimit = max(1, (int) $connection->retry_limit);
            $isDead = $attempts >= $retryLimit;
            $delaySeconds = min(3600, 30 * (2 ** max(0, $attempts - 1)));

            $event->update([
                'status' => $isDead ? 'dead' : 'failed',
                'locked_at' => null,
                'next_retry_at' => $isDead ? null : now()->addSeconds($delaySeconds),
                'last_error' => Str::limit($exception->getMessage(), 1000, ''),
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
        ]);
        return $event->fresh();
    }
}
