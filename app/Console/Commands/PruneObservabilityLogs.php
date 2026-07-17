<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\IntegrationInbox;
use App\Models\IntegrationOutbox;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PruneObservabilityLogs extends Command
{
    protected $signature = 'observability:prune {--dry-run : Hitung tanpa menghapus data}';

    protected $description = 'Pangkas audit dan event integrasi operasional yang sudah melewati masa simpan.';

    public function handle(): int
    {
        $auditBefore = now()->subDays((int) config('observability.audit_retention_days'));
        $integrationBefore = now()->subDays((int) config('observability.integration_retention_days'));

        $auditQuery = Schema::hasTable('audit_logs')
            ? AuditLog::query()->where('occurred_at', '<', $auditBefore)
            : null;
        $inboxQuery = Schema::hasTable('integration_inbox')
            ? IntegrationInbox::query()
                ->where('status', 'processed')
                ->whereNotNull('processed_at')
                ->where('processed_at', '<', $integrationBefore)
            : null;
        $outboxQuery = Schema::hasTable('integration_outbox')
            ? IntegrationOutbox::query()
                ->where('status', 'sent')
                ->where('sent_at', '<', $integrationBefore)
                // Payroll dan event selain operasional dipertahankan untuk rekonsiliasi.
                ->where(function ($query): void {
                    $query->where('event_type', 'like', 'attendance.%')
                        ->orWhere('event_type', 'like', 'employee.%')
                        ->orWhere('event_type', 'like', 'leave.%')
                        ->orWhere('event_type', 'like', 'loan.%');
                })
            : null;

        $counts = [
            'audit' => $auditQuery?->count() ?? 0,
            'inbox' => $inboxQuery?->count() ?? 0,
            'outbox operasional' => $outboxQuery?->count() ?? 0,
        ];

        foreach ($counts as $label => $count) {
            $this->line("{$label}: {$count}");
        }

        if ($this->option('dry-run')) {
            $this->info('Dry-run selesai. Tidak ada data yang dihapus.');
            return self::SUCCESS;
        }

        // Query delete dipakai langsung agar sifat immutable AuditLog tidak
        // dilanggar oleh model event; data hanya hilang sesuai retention .env.
        $auditQuery?->delete();
        $inboxQuery?->delete();
        $outboxQuery?->delete();

        $this->info('Pembersihan observability selesai. Data payroll dan event gagal/dead tetap disimpan.');
        return self::SUCCESS;
    }
}
