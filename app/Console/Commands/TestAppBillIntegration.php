<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use App\Services\Integration\AppBillIntegrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestAppBillIntegration extends Command
{
    protected $signature = 'integration:test-appbill {--company=}';
    protected $description = 'Mengirim satu event dummy untuk memverifikasi adapter AppBill tanpa jaringan.';

    public function handle(AppBillIntegrationService $service): int
    {
        $company = Company::query()
            ->active()
            ->when($this->option('company'), fn ($query, $id) => $query->whereKey((int) $id))
            ->first();

        if (! $company) {
            $this->error('Company aktif tidak ditemukan.');
            return self::FAILURE;
        }

        $userId = (int) (User::query()->value('id') ?? 0);
        $event = $service->queueTestEvent((int) $company->id, $userId);
        $event = $service->dispatch($event);
        $secondDispatch = $service->dispatch($event);
        $rawPayload = (string) DB::table('integration_outbox')->where('id', $event->id)->value('payload');
        $payloadEncrypted = ! str_contains($rawPayload, 'requested_by')
            && ! str_contains($rawPayload, 'system.connection.test');
        $idempotent = (int) $secondDispatch->attempts === (int) $event->attempts;

        $this->table(
            ['Event ID', 'Status', 'HTTP Mock', 'Attempts', 'Encrypted', 'Replay Safe'],
            [[
                $event->event_id,
                $event->status,
                $event->response_status,
                $event->attempts,
                $payloadEncrypted ? 'YES' : 'NO',
                $idempotent ? 'YES' : 'NO',
            ]]
        );

        return $event->status === 'sent' && $payloadEncrypted && $idempotent
            ? self::SUCCESS
            : self::FAILURE;
    }
}
