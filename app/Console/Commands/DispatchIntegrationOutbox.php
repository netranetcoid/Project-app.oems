<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Integration\AppBillIntegrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class DispatchIntegrationOutbox extends Command
{
    protected $signature = 'integration:dispatch-outbox {--company=} {--limit=50}';
    protected $description = 'Memproses antrean integrasi AppBill secara idempotent.';

    public function handle(AppBillIntegrationService $service): int
    {
        if (! Schema::hasTable('integration_outbox')) {
            $this->warn('Tabel integration_outbox belum tersedia.');
            return self::SUCCESS;
        }

        $query = Company::query()->active();
        if ($companyId = $this->option('company')) {
            $query->whereKey((int) $companyId);
        }

        foreach ($query->get() as $company) {
            $result = $service->dispatchPending((int) $company->id, (int) $this->option('limit'));
            $this->info("{$company->name}: {$result['processed']} diproses, {$result['sent']} terkirim.");
        }

        return self::SUCCESS;
    }
}

