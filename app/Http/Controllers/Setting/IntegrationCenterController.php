<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\IntegrationConnection;
use App\Models\IntegrationOutbox;
use App\Models\SystemHealthSnapshot;
use App\Models\Company;
use App\Services\Integration\AppBillIntegrationService;
use App\Services\Observability\SystemHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Throwable;

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
            'mode' => ['required', 'in:mock,live'],
            'base_url' => ['nullable', 'url', 'max:500'],
            'attendance_webhook_path' => ['nullable', 'string', 'max:255', 'regex:/^\\//'],
            'payroll_endpoint_path' => ['nullable', 'string', 'max:255', 'regex:/^\\//'],
            'connection_test_path' => ['nullable', 'string', 'max:255', 'regex:/^\\//'],
            'allow_inbound' => ['nullable', 'boolean'],
            'allow_outbound' => ['nullable', 'boolean'],
            'bpjs_payload_enabled' => ['nullable', 'boolean'],
            'confirm_live' => ['nullable', 'boolean'],
            'is_enabled' => ['nullable', 'boolean'],
            'cutover_at' => ['nullable', 'date'],
            'timeout_seconds' => ['required', 'integer', 'between:1,60'],
            'retry_limit' => ['required', 'integer', 'between:1,10'],
        ]);

        $company = Company::query()->findOrFail($connection->company_id);
        // Credentials are generated only by AppOEMS and then locked. This
        // avoids accidental token replacement from an ordinary settings save.
        $credentials = $connection->credentials ?? [];

        $mode = $data['mode'];
        if ($mode === 'live') {
            // Guardrail owner: request jaringan nyata hanya boleh aktif bila
            // endpoint, token, signature, dan cutover telah disetujui.
            abort_unless($request->user()->is_owner || $request->user()->is_super_admin, 403);
            if (! $request->boolean('confirm_live') || blank($data['base_url'] ?? $connection->base_url) || blank($credentials['api_token'] ?? null) || blank($credentials['hmac_secret'] ?? null)) {
                return back()->withErrors(['mode' => 'Mode live memerlukan URL HTTPS, token, HMAC secret, dan konfirmasi owner.'])->withInput();
            }
            if (parse_url((string) ($data['base_url'] ?? $connection->base_url), PHP_URL_SCHEME) !== 'https') {
                return back()->withErrors(['base_url' => 'Endpoint AppBill live wajib memakai HTTPS.'])->withInput();
            }
        }

        $existingSettings = $connection->settings ?? [];
        $settings = array_merge($existingSettings, [
            'company_code' => $company->code,
            'attendance_webhook_path' => $data['attendance_webhook_path'] ?: ($existingSettings['attendance_webhook_path'] ?? '/api/integrations/attendance/webhook'),
            'payroll_endpoint_path' => $data['payroll_endpoint_path'] ?: ($existingSettings['payroll_endpoint_path'] ?? '/api/v1/integrations/appoems/payroll-periods'),
            // This endpoint is a handshake only. It must never be reused for
            // payroll or attendance, so the live-test button is non-financial.
            'connection_test_path' => $data['connection_test_path'] ?: ($existingSettings['connection_test_path'] ?? '/api/v1/integrations/appoems/connection-test'),
            'dummy_only' => $mode === 'mock',
            'live_activation_confirmed' => $mode === 'live',
            // Payroll inti selalu memakai outbound umum. Toggle ini hanya
            // menentukan apakah nominal BPJS dilampirkan ke payload AppBill.
            'bpjs_payload_enabled' => $request->boolean('bpjs_payload_enabled'),
        ]);

        $connection->update([
            'mode' => $mode,
            'base_url' => $data['base_url'] ?: $connection->base_url,
            'auth_type' => $mode === 'live' ? 'bearer_hmac' : 'none',
            'credentials' => $credentials,
            'is_enabled' => $request->boolean('is_enabled'),
            'allow_inbound' => $request->boolean('allow_inbound'),
            'allow_outbound' => $request->boolean('allow_outbound'),
            'cutover_at' => $data['cutover_at'] ?? null,
            'timeout_seconds' => $data['timeout_seconds'],
            'retry_limit' => $data['retry_limit'],
            'settings' => $settings,
            'health_status' => $mode === 'live' ? 'not_configured' : 'ready',
        ]);

        $cancelled = 0;
        if (! $request->boolean('bpjs_payload_enabled')) {
            $cancelled = $this->appBill->redactPendingPayrollBpjs($connection);
        }

        $message = $mode === 'live'
            ? 'Konfigurasi live AppBill tersimpan. Jalankan uji staging sebelum cutover.'
            : 'Konfigurasi dummy AppBill diperbarui; tidak ada data keluar ke jaringan.';
        if (! $request->boolean('bpjs_payload_enabled')) {
            $message .= $cancelled > 0
                ? " Payroll normal tetap dikirim; rincian BPJS dihapus dari {$cancelled} antrean payroll yang belum terkirim."
                : ' Payroll normal tetap dikirim tanpa rincian BPJS.';
        }

        return back()->with('success', $message);
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

    /**
     * Runs one signed HTTPS handshake immediately in the web request.
     * It is owner-only and does not use Laravel queue, scheduler, or outbox.
     */
    public function testLiveDirect(Request $request, IntegrationConnection $connection): RedirectResponse
    {
        $this->ensureCompany($connection);
        abort_unless($request->user()->is_owner || $request->user()->is_super_admin, 403);

        try {
            $result = $this->appBill->testLiveConnection($connection);
            $connection->update([
                'health_status' => 'ready',
                'last_success_at' => now(),
            ]);

            return back()->with('success', "Koneksi live AppBill terverifikasi langsung (HTTP {$result['status']}). Tidak ada data absensi atau payroll yang dikirim.");
        } catch (Throwable $exception) {
            $connection->update([
                'health_status' => 'warning',
                'last_failure_at' => now(),
            ]);

            return back()->with('error', 'Koneksi live AppBill gagal: ' . Str::limit($exception->getMessage(), 300, ''));
        }
    }

    /**
     * Kredensial integrasi hanya dapat dirotasi oleh owner/super admin. Nilai
     * hanya di-flash sekali ke session, sementara database menyimpannya dengan
     * encrypted cast. Rotasi tidak pernah mengaktifkan mode LIVE otomatis.
     */
    public function generateCredentials(Request $request, IntegrationConnection $connection): RedirectResponse
    {
        $this->ensureCompany($connection);
        // Only the designated developer account may create the secret pair.
        // Owner still approves live/cutover but never sees the raw values.
        abort_unless($request->user()->is_developer, 403);

        if ($this->credentialsAreLocked($connection)) {
            return back()->with('error', 'Token dan HMAC AppBill sudah dikunci. Tidak dapat dibuat ulang dari aplikasi.');
        }

        $credentials = array_merge($connection->credentials ?? [], [
            'api_token' => Str::random(64),
            'hmac_secret' => bin2hex(random_bytes(32)),
        ]);
        $settings = array_merge($connection->settings ?? [], [
            // This flag is a deliberate owner decision. The pair is also
            // checked directly so legacy credentials are protected as well.
            'credentials_locked' => true,
        ]);
        $connection->update([
            'credentials' => $credentials,
            'settings' => $settings,
        ]);

        return back()->with('success', 'Token dan HMAC dibuat lalu dikunci. Developer harus memasukkan password untuk melihat nilainya.');
    }

    /**
     * Reveals the encrypted pair only to the designated developer after an
     * explicit password re-check. Values are flashed once and never written to
     * audit logs, response JSON, or the normal settings form.
     */
    public function revealCredentials(Request $request, IntegrationConnection $connection): RedirectResponse
    {
        $this->ensureCompany($connection);
        abort_unless($request->user()->is_developer, 403);

        $data = $request->validate([
            'developer_password' => ['required', 'string', 'max:255'],
        ]);
        if (! Hash::check($data['developer_password'], (string) $request->user()->password)) {
            return back()->withErrors(['developer_password' => 'Password Developer tidak cocok.']);
        }

        $credentials = $connection->credentials ?? [];
        if (! filled($credentials['api_token'] ?? null) || ! filled($credentials['hmac_secret'] ?? null)) {
            return back()->with('error', 'Token dan HMAC AppBill belum dibuat.');
        }

        return back()
            ->with('appbill_revealed_credentials', [
                'api_token' => $credentials['api_token'],
                'hmac_secret' => $credentials['hmac_secret'],
            ])
            ->with('success', 'Kredensial dibuka satu kali untuk sesi Developer ini.')
            ->header('Cache-Control', 'no-store, private');
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

    /**
     * A complete credential pair is always treated as immutable, including
     * connections created before the explicit credentials_locked flag existed.
     */
    private function credentialsAreLocked(IntegrationConnection $connection): bool
    {
        $credentials = $connection->credentials ?? [];

        return (bool) data_get($connection->settings, 'credentials_locked', false)
            || (filled($credentials['api_token'] ?? null) && filled($credentials['hmac_secret'] ?? null));
    }
}
