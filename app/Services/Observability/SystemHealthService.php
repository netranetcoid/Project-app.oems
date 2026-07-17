<?php

namespace App\Services\Observability;

use App\Models\IntegrationConnection;
use App\Models\IntegrationOutbox;
use App\Models\SystemHealthSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class SystemHealthService
{
    public function check(int $companyId, bool $persist = true): array
    {
        $checks = [];

        $checks[] = $this->capture('database', function (): array {
            DB::selectOne('SELECT 1 AS healthy');
            return ['status' => 'ok', 'message' => 'Database terhubung dan merespons.', 'metrics' => []];
        });

        $checks[] = $this->capture('cache', function (): array {
            $key = 'health:' . Str::uuid();
            Cache::put($key, 'ok', 30);
            $works = Cache::get($key) === 'ok';
            Cache::forget($key);
            if (! $works) {
                throw new \RuntimeException('Cache gagal membaca nilai uji.');
            }
            return ['status' => 'ok', 'message' => 'Cache baca/tulis normal.', 'metrics' => []];
        });

        $checks[] = $this->capture('storage', function (): array {
            $path = storage_path('framework');
            if (! is_dir($path) || ! is_writable($path)) {
                throw new \RuntimeException('Folder storage/framework tidak dapat ditulis.');
            }
            return ['status' => 'ok', 'message' => 'Storage aplikasi dapat ditulis.', 'metrics' => []];
        });

        $checks[] = $this->capture('appbill', function () use ($companyId): array {
            $connection = IntegrationConnection::forCompany($companyId)->where('provider', 'appbill')->first();
            if (! $connection) {
                return ['status' => 'warning', 'message' => 'Koneksi AppBill belum dibuat.', 'metrics' => []];
            }
            if (! $connection->is_enabled) {
                return [
                    'status' => 'warning',
                    'message' => 'Simulasi AppBill sedang dinonaktifkan.',
                    'metrics' => ['mode' => $connection->mode, 'enabled' => false],
                ];
            }
            $message = $connection->mode === 'mock'
                ? 'Mode dummy aktif; tidak ada data keluar ke jaringan.'
                : 'Mode live menunggu validasi endpoint dan cutover owner.';
            return [
                'status' => $connection->mode === 'mock' ? 'ok' : 'warning',
                'message' => $message,
                'metrics' => ['mode' => $connection->mode, 'enabled' => $connection->is_enabled],
            ];
        });

        $checks[] = $this->capture('outbox', function () use ($companyId): array {
            $pending = IntegrationOutbox::forCompany($companyId)->whereIn('status', ['pending', 'failed'])->count();
            $dead = IntegrationOutbox::forCompany($companyId)->where('status', 'dead')->count();
            return [
                'status' => $dead > 0 ? 'warning' : 'ok',
                'message' => $dead > 0 ? 'Ada event gagal permanen yang perlu diperiksa.' : 'Antrean integrasi dalam kondisi normal.',
                'metrics' => ['pending' => $pending, 'dead' => $dead],
            ];
        });

        if ($persist && Schema::hasTable('system_health_snapshots')) {
            foreach ($checks as $check) {
                SystemHealthSnapshot::create([
                    'company_id' => $companyId,
                    'component' => $check['component'],
                    'status' => $check['status'],
                    'message' => $check['message'],
                    'metrics' => $check['metrics'],
                    'checked_at' => now(),
                ]);
            }
        }

        return $checks;
    }

    private function capture(string $component, callable $callback): array
    {
        try {
            return ['component' => $component, ...$callback()];
        } catch (Throwable $exception) {
            return [
                'component' => $component,
                'status' => 'error',
                'message' => Str::limit($exception->getMessage(), 500, ''),
                'metrics' => [],
            ];
        }
    }
}
