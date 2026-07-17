<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Observability\SystemHealthService;
use Illuminate\Console\Command;

class CheckSystemHealth extends Command
{
    protected $signature = 'system:health-check {--company=}';
    protected $description = 'Memeriksa database, cache, storage, AppBill, dan outbox.';

    public function handle(SystemHealthService $service): int
    {
        $company = Company::query()
            ->active()
            ->when($this->option('company'), fn ($query, $id) => $query->whereKey((int) $id))
            ->first();

        if (! $company) {
            $this->error('Company aktif tidak ditemukan.');
            return self::FAILURE;
        }

        $checks = $service->check((int) $company->id);
        $this->table(
            ['Component', 'Status', 'Message'],
            collect($checks)->map(fn (array $check): array => [
                $check['component'], strtoupper($check['status']), $check['message'],
            ])->all()
        );

        return collect($checks)->contains('status', 'error')
            ? self::FAILURE
            : self::SUCCESS;
    }
}

