@extends('layouts/layoutMaster')

@section('title', 'Integrasi & Audit Sistem')

@section('content')
@php($appBillCredentialsLocked = (bool) data_get($connection->settings, 'credentials_locked', false) || (filled(data_get($connection->credentials, 'api_token')) && filled(data_get($connection->credentials, 'hmac_secret'))))
@php($canRevealAppBillCredentials = (bool) auth()->user()->is_developer)
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div>
    <h4 class="mb-1">Integrasi & Audit Sistem</h4>
    <p class="text-muted mb-0">Pusat dummy AppBill, antrean aman, audit aktivitas, dan kesehatan AppOEMS.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    @can('health.view')
      <form method="POST" action="{{ route('settings.integrations.health') }}">@csrf
        <button class="btn btn-label-info"><i class="ri ri-pulse-line me-1"></i>Periksa Sistem</button>
      </form>
    @endcan
    @can('integration.dispatch')
      <form method="POST" action="{{ route('settings.integrations.dispatch') }}">@csrf
        <button class="btn btn-primary"><i class="ri ri-play-circle-line me-1"></i>Proses Antrean</button>
      </form>
    @endcan
  </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if(session('appbill_revealed_credentials'))
  <div class="alert alert-warning">
    <div class="fw-bold mb-2">Simpan kredensial AppBill sekarang — hanya tampil sekali.</div>
    <div class="mb-1">API token: <code class="text-break">{{ data_get(session('appbill_revealed_credentials'),'api_token') }}</code></div>
    <div>HMAC secret: <code class="text-break">{{ data_get(session('appbill_revealed_credentials'),'hmac_secret') }}</code></div>
    <div class="small mt-2">Kirim melalui kanal aman ke pengembang AppBill; jangan simpan di chat, screenshot, atau dokumen publik.</div>
  </div>
@endif

@if($connection->mode === 'live')
  <div class="alert alert-success d-flex align-items-start gap-3">
    <i class="ri ri-broadcast-line fs-4"></i>
    <div><strong>Mode AppBill live aktif.</strong><br><span class="small">Gunakan Uji Koneksi Live untuk handshake langsung tanpa antrean. Pengiriman absensi/payroll tetap mengikuti approval dan outbox audit.</span></div>
  </div>
@else
  <div class="alert alert-primary d-flex align-items-start gap-3">
    <i class="ri ri-shield-check-line fs-4"></i>
    <div><strong>Mode dummy aman sedang aktif.</strong><br><span class="small">Tidak ada data yang dikirim ke internet. Mode live dikunci sampai API, signature, IP allowlist, dan tanggal cutover disetujui owner.</span></div>
  </div>
@endif

<div class="row g-4 mb-4">
  @foreach([
    ['Antrean', $stats['pending'], 'warning', 'ri-time-line'],
    [$connection->mode === 'live' ? 'Terkirim Live' : 'Terkirim Mock', $stats['sent'], 'success', 'ri-checkbox-circle-line'],
    ['Perlu Tindakan', $stats['dead'], 'danger', 'ri-error-warning-line'],
    ['Audit Hari Ini', $stats['audit_today'], 'info', 'ri-history-line'],
  ] as [$label,$value,$color,$icon])
  <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body d-flex align-items-center gap-3">
    <span class="avatar-initial rounded bg-label-{{ $color }} p-3"><i class="ri {{ $icon }} fs-4"></i></span>
    <div><div class="text-muted small">{{ $label }}</div><h4 class="mb-0">{{ number_format($value) }}</h4></div>
  </div></div></div>
  @endforeach
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-5">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div><h5 class="mb-1">Koneksi AppBill</h5><small class="text-muted">Konfigurasi dapat diedit tanpa membuka akses live.</small></div>
        <span class="badge bg-label-primary">{{ strtoupper($connection->mode) }}</span>
      </div>
      <div class="card-body">
        @can('integration.manage')
        <form method="POST" action="{{ route('settings.integrations.update', $connection) }}" class="row g-3">
          @csrf @method('PUT')
          <div class="col-12">
            <label class="form-label">Provider</label>
            <input class="form-control" value="AppBill — Dummy Adapter" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Mode koneksi</label>
            <select class="form-select" name="mode" id="appBillMode">
              <option value="mock" @selected(old('mode',$connection->mode) === 'mock')>Mock / Dummy aman</option>
              <option value="live" @selected(old('mode',$connection->mode) === 'live')>Live AppBill (owner)</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Company code</label>
            <input class="form-control" value="{{ data_get($connection->settings,'company_code',session('company_code') ?: 'OEMS') }}" disabled>
            <div class="form-text">Dikirim pada header <code>X-Company-Code</code>.</div>
          </div>
          <div class="col-12 appbill-live-field">
            <label class="form-label">Base URL AppBill</label>
            <input class="form-control" type="url" name="base_url" value="{{ old('base_url',$connection->base_url) }}" placeholder="https://staging.appbill.example">
          </div>
          @if($canRevealAppBillCredentials)
          <div class="col-12 appbill-live-field">
            @if($appBillCredentialsLocked)
              <div class="alert alert-success mb-0 py-2 d-flex flex-wrap align-items-center justify-content-between gap-2"><span><i class="ri ri-lock-line me-1"></i><strong>Token dan HMAC sudah dikunci.</strong> Hanya Developer yang dapat melihatnya setelah password diverifikasi.</span><button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#revealAppBillCredentialsModal"><i class="ri ri-eye-line me-1"></i>Lihat Kredensial</button></div>
            @else
              <button type="submit" form="appbillCredentialsForm" class="btn btn-outline-warning btn-sm"><i class="ri ri-key-2-line me-1"></i>Buat Token Dua Arah & Kunci</button>
              <span class="small text-muted ms-2">Developer-only; tersimpan terenkripsi dan dikunci setelah dibuat.</span>
            @endif
          </div>
          @endif
          <div class="col-md-6 appbill-live-field">
            <label class="form-label">Path webhook absensi</label>
            <input class="form-control" name="attendance_webhook_path" value="{{ old('attendance_webhook_path',data_get($connection->settings,'attendance_webhook_path','/api/integrations/attendance/webhook')) }}">
          </div>
          <div class="col-md-6 appbill-live-field">
            <label class="form-label">Path endpoint payroll</label>
            <input class="form-control" name="payroll_endpoint_path" value="{{ old('payroll_endpoint_path',data_get($connection->settings,'payroll_endpoint_path','/api/v1/integrations/appoems/payroll-periods')) }}">
          </div>
          <div class="col-12 appbill-live-field">
            <label class="form-label">Path uji koneksi langsung</label>
            <input class="form-control" name="connection_test_path" value="{{ old('connection_test_path',data_get($connection->settings,'connection_test_path','/api/v1/integrations/appoems/connection-test')) }}">
            <div class="form-text">Endpoint AppBill untuk handshake teknis. Tidak menerima atau membuat data absensi/payroll.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Timeout (detik)</label>
            <input class="form-control" type="number" min="1" max="60" name="timeout_seconds" value="{{ old('timeout_seconds',$connection->timeout_seconds) }}" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Maksimal percobaan</label>
            <input class="form-control" type="number" min="1" max="10" name="retry_limit" value="{{ old('retry_limit',$connection->retry_limit) }}" required>
          </div>
          <div class="col-12">
            <label class="form-label">Rencana cutover <span class="text-muted">(opsional)</span></label>
            <input class="form-control" type="datetime-local" name="cutover_at" value="{{ old('cutover_at',$connection->cutover_at?->format('Y-m-d\TH:i')) }}">
          </div>
          <div class="col-12">
            <input type="hidden" name="is_enabled" value="0">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="connectionEnabled" @checked($connection->is_enabled)>
              <label class="form-check-label" for="connectionEnabled">Aktifkan simulasi outbound</label>
            </div>
          </div>
          <div class="col-md-6">
            <input type="hidden" name="allow_outbound" value="0">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="allow_outbound" value="1" id="allowOutbound" @checked(old('allow_outbound',$connection->allow_outbound))>
              <label class="form-check-label" for="allowOutbound">Izinkan AppOEMS mengirim event</label>
            </div>
          </div>
          <div class="col-md-6">
            <input type="hidden" name="allow_inbound" value="0">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="allow_inbound" value="1" id="allowInbound" @checked(old('allow_inbound',$connection->allow_inbound))>
              <label class="form-check-label" for="allowInbound">Izinkan AppBill kirim perubahan</label>
            </div>
          </div>
          <div class="col-12 appbill-live-field">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="confirm_live" value="1" id="confirmLive" @checked(old('confirm_live',false))>
              <label class="form-check-label" for="confirmLive">Saya owner menyetujui endpoint, token, HMAC, allowlist, dan cutover live.</label>
            </div>
          </div>
          <div class="col-12 d-flex flex-wrap gap-2">
            <button class="btn btn-primary">Simpan Pengaturan</button>
          </div>
        </form>
        @if($canRevealAppBillCredentials && ! $appBillCredentialsLocked)
          <form id="appbillCredentialsForm" method="POST" action="{{ route('settings.integrations.credentials',$connection) }}" onsubmit="return confirm('Buat token dan HMAC satu kali lalu kunci permanen dari aplikasi? Simpan nilainya sebelum menutup halaman.');">@csrf</form>
        @endif
        @endcan

        @can('integration.dispatch')
        <hr>
        @if($connection->mode === 'live' && (auth()->user()->is_owner || auth()->user()->is_super_admin))
          <form method="POST" action="{{ route('settings.integrations.test-live-direct',$connection) }}" onsubmit="return confirm('Jalankan handshake langsung ke AppBill? Tidak ada absensi maupun payroll yang dikirim.');">
            @csrf
            <button class="btn btn-success w-100 mb-2"><i class="ri ri-links-line me-1"></i>Uji Koneksi Live Sekarang</button>
          </form>
        @endif
        <form method="POST" action="{{ route('settings.integrations.test') }}">@csrf
          <button class="btn btn-label-success w-100"><i class="ri ri-flask-line me-1"></i>Kirim Event Tes Dummy</button>
        </form>
        @endcan
      </div>
    </div>
  </div>

  <div class="col-xl-7">
    <div class="card h-100">
      <div class="card-header"><h5 class="mb-1">Kesehatan Sistem</h5><small class="text-muted">Pemeriksaan tidak menampilkan password, token, GPS, atau payload payroll.</small></div>
      <div class="card-body">
        <div class="row g-3">
          @foreach($healthChecks as $check)
            @php($healthColor = match($check['status']) {'ok'=>'success','warning'=>'warning',default=>'danger'})
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <strong class="text-capitalize">{{ str_replace('_',' ',$check['component']) }}</strong>
                  <span class="badge bg-label-{{ $healthColor }}">{{ strtoupper($check['status']) }}</span>
                </div>
                <div class="small text-muted">{{ $check['message'] }}</div>
                @if(!empty($check['metrics']))<div class="small mt-2">{{ collect($check['metrics'])->map(fn($v,$k)=>"$k: ".(is_bool($v)?($v?'ya':'tidak'):$v))->implode(' • ') }}</div>@endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header"><h5 class="mb-1">Outbox Integrasi</h5><small class="text-muted">Idempotency mencegah event payroll terkirim dua kali.</small></div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Event</th><th>Jenis</th><th>Status</th><th>Percobaan</th><th>Respons</th><th>Waktu</th><th></th></tr></thead>
      <tbody>
      @forelse($events as $event)
        @php($eventColor = match($event->status) {'sent'=>'success','dead'=>'danger','failed'=>'warning','processing'=>'info',default=>'secondary'})
        <tr>
          <td><code>{{ Illuminate\Support\Str::limit($event->event_id,18) }}</code><div class="small text-muted">{{ Illuminate\Support\Str::limit($event->idempotency_key,42) }}</div></td>
          <td>{{ $event->event_type }}<div class="small text-muted">{{ $event->aggregate_type ? class_basename($event->aggregate_type).' #'.$event->aggregate_id : '-' }}</div></td>
          <td><span class="badge bg-label-{{ $eventColor }}">{{ strtoupper($event->status) }}</span></td>
          <td>{{ $event->attempts }} / {{ $event->connection?->retry_limit ?? 0 }}</td>
          <td><span class="small">{{ data_get($event->response_summary,'code',$event->last_error ?: '-') }}</span></td>
          <td class="small">{{ $event->created_at?->format('d/m/Y H:i:s') }}</td>
          <td>@can('integration.dispatch') @if($event->status !== 'sent')<form method="POST" action="{{ route('settings.integrations.retry',$event) }}">@csrf<button class="btn btn-sm btn-label-primary">Ulangi</button></form>@endif @endcan</td>
        </tr>
      @empty<tr><td colspan="7" class="text-center text-muted py-5">Belum ada event integrasi.</td></tr>@endforelse
      </tbody>
    </table>
  </div>
  <div class="card-body">{{ $events->links() }}</div>
</div>

@can('audit.view')
<div class="card">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div><h5 class="mb-1">Audit Aktivitas</h5><small class="text-muted">Append-only, tenant-scoped, dan tidak menyimpan nilai sensitif.</small></div>
    <form method="GET" class="d-flex gap-2"><input class="form-control form-control-sm" name="audit_search" value="{{ request('audit_search') }}" placeholder="Route / request ID"><button class="btn btn-sm btn-label-primary">Cari</button></form>
  </div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Status</th><th>Field</th><th>Request ID</th></tr></thead>
      <tbody>
      @forelse($audits as $audit)
        <tr>
          <td class="small text-nowrap">{{ $audit->occurred_at?->format('d/m/Y H:i:s') }}</td>
          <td>{{ $audit->user?->name ?? 'System/Guest' }}</td>
          <td><strong>{{ $audit->action }}</strong><div class="small text-muted">{{ $audit->method }} {{ Illuminate\Support\Str::limit($audit->path,50) }}</div></td>
          <td><span class="badge bg-label-{{ ($audit->response_status ?? 500) < 400 ? 'success' : 'danger' }}">{{ $audit->response_status ?? '-' }}</span></td>
          <td class="small">{{ collect($audit->changed_fields ?? [])->implode(', ') ?: '-' }}</td>
          <td><code>{{ Illuminate\Support\Str::limit($audit->request_id,13) }}</code></td>
        </tr>
      @empty<tr><td colspan="6" class="text-center text-muted py-5">Audit akan muncul setelah ada perubahan data.</td></tr>@endforelse
      </tbody>
    </table>
  </div>
  <div class="card-body">{{ $audits->links() }}</div>
</div>
@endcan

@if($canRevealAppBillCredentials && $appBillCredentialsLocked)
<div class="modal fade" id="revealAppBillCredentialsModal" tabindex="-1" aria-labelledby="revealAppBillCredentialsLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="POST" action="{{ route('settings.integrations.credentials.reveal',$connection) }}" autocomplete="off">
      @csrf
      <div class="modal-header"><h5 class="modal-title" id="revealAppBillCredentialsLabel">Konfirmasi Password Developer</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
      <div class="modal-body"><p class="text-muted small">Token dan HMAC akan ditampilkan satu kali pada halaman ini. Jangan gunakan password Owner atau pegawai.</p><label class="form-label" for="developerPassword">Password Developer</label><input id="developerPassword" class="form-control" type="password" name="developer_password" required autofocus></div>
      <div class="modal-footer"><button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="ri ri-lock-unlock-line me-1"></i>Buka Kredensial</button></div>
    </form>
  </div>
</div>
@endif
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const mode = document.getElementById('appBillMode');
    const fields = document.querySelectorAll('.appbill-live-field');
    const refresh = () => fields.forEach((field) => field.classList.toggle('d-none', mode.value !== 'live'));
    mode?.addEventListener('change', refresh);
    refresh();
  });
</script>
@endsection
