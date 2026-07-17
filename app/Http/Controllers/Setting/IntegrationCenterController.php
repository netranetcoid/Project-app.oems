<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\IntegrationConnection;
use App\Models\IntegrationOutbox;
use App\Models\SystemHealthSnapshot;
use App\Services\Integration\AppBillIntegrationService;
use App\Services\Observability\SystemHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationCenterController extends Controller
{
    public function __construct(
        private AppBillIntegrationService $appBill,
        private SystemHealthService $health
    ) {}

    public function index(Request $request): View
    {
        $companyId = (int) session('company_id');
        $connection = $this->appBill->connection($companyId);
        $healthChecks = SystemHealthSnapshot::forCompany($companyId)
            ->whereIn('id', function ($query) use ($companyId): void {
                $query->from('system_health_snapshots as latest')
                    ->selectRaw('MAX(latest.id)')
                    ->where('latest.company_id', $companyId)
                    ->groupBy('latest.component');
            })
            ->orderBy('component')
            ->get()
            ->map(fn ($item): array => [
                'component' => $item->component,
                'status' => $item->status,
                'message' => $item->message,
                'metrics' => $item->metrics ?? [],
                'checked_at' => $item->checked_at,
            ])
            ->all();

        if ($healthChecks === []) {
            $healthChecks = $this->health->check($companyId, false);
        }

        $auditQuery = AuditLog::forCompany($companyId)->with('user')->latest('occurred_at');
        if ($search = trim((string) $request->query('audit_search'))) {
            $auditQuery->where(function ($query) use ($search): void {
                $query->where('action', 'like', "%$search%")
                    ->orWhere('route_name', 'like', "%$search%")
                    ->orWhere('request_id', 'like', "%$search%");
            });
        }

        return view('setting.integrations.index', [
            'connection' => $connection,
            'healthChecks' => $healthChecks,
            'events' => IntegrationOutbox::forCompany($companyId)
                ->with('connection')
                ->latest()
                ->paginate(15, ['*'], 'outbox')
                ->withQueryString(),
            'audits' => $auditQuery
                ->paginate(20, ['*'], 'audit')
                ->withQueryString(),
            'stats' => [
                'pending' => IntegrationOutbox::forCompany($companyId)->whereIn('status', ['pending', 'failed'])->count(),
                'sent' => IntegrationOutbox::forCompany($companyId)->where('status', 'sent')->count(),
                'dead' => IntegrationOutbox::forCompany($companyId)->where('status', 'dead')->count(),
                'audit_today' => AuditLog::forCompany($companyId)->whereDate('occurred_at', today())->count(),
            ],
        ]);
    }

    public function update(Request $request, IntegrationConnection $connection): RedirectResponse
    {
        $this->ensureCompany($connection);
        $data = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'cutover_at' => ['nullable', 'date'],
            'timeout_seconds' => ['required', 'integer', 'between:1,60'],
            'retry_limit' => ['required', 'integer', 'between:1,10'],
        ]);

        // Mode live tidak tersedia di form. API asli baru dapat diaktifkan
        // setelah endpoint, signature, allowlist dan cutover disetujui owner.
        $connection->update([
            'mode' => 'mock',
            'base_url' => null,
            'auth_type' => 'none',
            'is_enabled' => $request->boolean('is_enabled'),
            'cutover_at' => $data['cutover_at'] ?? null,
            'timeout_seconds' => $data['timeout_seconds'],
            'retry_limit' => $data['retry_limit'],
            'health_status' => 'ready',
        ]);

        return back()->with('success', 'Konfigurasi dummy AppBill diperbarui. Mode live tetap terkunci.');
    }

    public function queueTest(Request $request): RedirectResponse
    {
        $event = $this->appBill->queueTestEvent((int) session('company_id'), (int) $request->user()->id);
        $event = $this->appBill->dispatch($event);

        return back()->with(
            $event->status === 'sent' ? 'success' : 'error',
            $event->status === 'sent'
                ? "Tes dummy berhasil. Event {$event->event_id} diterima."
                : "Tes dummy gagal: {$event->last_error}"
        );
    }

    public function dispatch(): RedirectResponse
    {
        $result = $this->appBill->dispatchPending((int) session('company_id'));
        return back()->with('success', "{$result['processed']} event diproses; {$result['sent']} berhasil.");
    }

    public function retry(IntegrationOutbox $event): RedirectResponse
    {
        abort_if((int) $event->company_id !== (int) session('company_id'), 403);
        $this->appBill->retry($event);
        return back()->with('success', 'Event dimasukkan kembali ke antrean.');
    }

    public function refreshHealth(): RedirectResponse
    {
        $this->health->check((int) session('company_id'));
        return back()->with('success', 'Pemeriksaan kesehatan sistem selesai.');
    }

    private function ensureCompany(IntegrationConnection $connection): void
    {
        abort_if((int) $connection->company_id !== (int) session('company_id'), 403);
    }
}

