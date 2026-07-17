<?php $__env->startSection('title', 'Integrasi & Audit Sistem'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div>
    <h4 class="mb-1">Integrasi & Audit Sistem</h4>
    <p class="text-muted mb-0">Pusat dummy AppBill, antrean aman, audit aktivitas, dan kesehatan AppOEMS.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('health.view')): ?>
      <form method="POST" action="<?php echo e(route('settings.integrations.health')); ?>"><?php echo csrf_field(); ?>
        <button class="btn btn-label-info"><i class="ri ri-pulse-line me-1"></i>Periksa Sistem</button>
      </form>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('integration.dispatch')): ?>
      <form method="POST" action="<?php echo e(route('settings.integrations.dispatch')); ?>"><?php echo csrf_field(); ?>
        <button class="btn btn-primary"><i class="ri ri-play-circle-line me-1"></i>Proses Antrean</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
<?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>
<?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

<div class="alert alert-primary d-flex align-items-start gap-3">
  <i class="ri ri-shield-check-line fs-4"></i>
  <div><strong>Mode dummy aman sedang aktif.</strong><br><span class="small">Tidak ada data yang dikirim ke internet. Mode live dikunci sampai API, signature, IP allowlist, dan tanggal cutover disetujui owner.</span></div>
</div>

<div class="row g-4 mb-4">
  <?php $__currentLoopData = [
    ['Antrean', $stats['pending'], 'warning', 'ri-time-line'],
    ['Terkirim Mock', $stats['sent'], 'success', 'ri-checkbox-circle-line'],
    ['Perlu Tindakan', $stats['dead'], 'danger', 'ri-error-warning-line'],
    ['Audit Hari Ini', $stats['audit_today'], 'info', 'ri-history-line'],
  ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label,$value,$color,$icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="col-6 col-xl-3"><div class="card h-100"><div class="card-body d-flex align-items-center gap-3">
    <span class="avatar-initial rounded bg-label-<?php echo e($color); ?> p-3"><i class="ri <?php echo e($icon); ?> fs-4"></i></span>
    <div><div class="text-muted small"><?php echo e($label); ?></div><h4 class="mb-0"><?php echo e(number_format($value)); ?></h4></div>
  </div></div></div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-5">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div><h5 class="mb-1">Koneksi AppBill</h5><small class="text-muted">Konfigurasi dapat diedit tanpa membuka akses live.</small></div>
        <span class="badge bg-label-primary"><?php echo e(strtoupper($connection->mode)); ?></span>
      </div>
      <div class="card-body">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('integration.manage')): ?>
        <form method="POST" action="<?php echo e(route('settings.integrations.update', $connection)); ?>" class="row g-3">
          <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
          <div class="col-12">
            <label class="form-label">Provider</label>
            <input class="form-control" value="AppBill — Dummy Adapter" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Timeout (detik)</label>
            <input class="form-control" type="number" min="1" max="60" name="timeout_seconds" value="<?php echo e(old('timeout_seconds',$connection->timeout_seconds)); ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Maksimal percobaan</label>
            <input class="form-control" type="number" min="1" max="10" name="retry_limit" value="<?php echo e(old('retry_limit',$connection->retry_limit)); ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Rencana cutover <span class="text-muted">(opsional)</span></label>
            <input class="form-control" type="datetime-local" name="cutover_at" value="<?php echo e(old('cutover_at',$connection->cutover_at?->format('Y-m-d\TH:i'))); ?>">
          </div>
          <div class="col-12">
            <input type="hidden" name="is_enabled" value="0">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="connectionEnabled" <?php if($connection->is_enabled): echo 'checked'; endif; ?>>
              <label class="form-check-label" for="connectionEnabled">Aktifkan simulasi outbound</label>
            </div>
          </div>
          <div class="col-12 d-flex flex-wrap gap-2">
            <button class="btn btn-primary">Simpan Pengaturan</button>
          </div>
        </form>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('integration.dispatch')): ?>
        <hr>
        <form method="POST" action="<?php echo e(route('settings.integrations.test')); ?>"><?php echo csrf_field(); ?>
          <button class="btn btn-label-success w-100"><i class="ri ri-flask-line me-1"></i>Kirim Event Tes Dummy</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-7">
    <div class="card h-100">
      <div class="card-header"><h5 class="mb-1">Kesehatan Sistem</h5><small class="text-muted">Pemeriksaan tidak menampilkan password, token, GPS, atau payload payroll.</small></div>
      <div class="card-body">
        <div class="row g-3">
          <?php $__currentLoopData = $healthChecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php ($healthColor = match($check['status']) {'ok'=>'success','warning'=>'warning',default=>'danger'}); ?>
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <strong class="text-capitalize"><?php echo e(str_replace('_',' ',$check['component'])); ?></strong>
                  <span class="badge bg-label-<?php echo e($healthColor); ?>"><?php echo e(strtoupper($check['status'])); ?></span>
                </div>
                <div class="small text-muted"><?php echo e($check['message']); ?></div>
                <?php if(!empty($check['metrics'])): ?><div class="small mt-2"><?php echo e(collect($check['metrics'])->map(fn($v,$k)=>"$k: ".(is_bool($v)?($v?'ya':'tidak'):$v))->implode(' • ')); ?></div><?php endif; ?>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
      <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php ($eventColor = match($event->status) {'sent'=>'success','dead'=>'danger','failed'=>'warning','processing'=>'info',default=>'secondary'}); ?>
        <tr>
          <td><code><?php echo e(Illuminate\Support\Str::limit($event->event_id,18)); ?></code><div class="small text-muted"><?php echo e(Illuminate\Support\Str::limit($event->idempotency_key,42)); ?></div></td>
          <td><?php echo e($event->event_type); ?><div class="small text-muted"><?php echo e($event->aggregate_type ? class_basename($event->aggregate_type).' #'.$event->aggregate_id : '-'); ?></div></td>
          <td><span class="badge bg-label-<?php echo e($eventColor); ?>"><?php echo e(strtoupper($event->status)); ?></span></td>
          <td><?php echo e($event->attempts); ?> / <?php echo e($event->connection?->retry_limit ?? 0); ?></td>
          <td><span class="small"><?php echo e(data_get($event->response_summary,'code',$event->last_error ?: '-')); ?></span></td>
          <td class="small"><?php echo e($event->created_at?->format('d/m/Y H:i:s')); ?></td>
          <td><?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('integration.dispatch')): ?> <?php if($event->status !== 'sent'): ?><form method="POST" action="<?php echo e(route('settings.integrations.retry',$event)); ?>"><?php echo csrf_field(); ?><button class="btn btn-sm btn-label-primary">Ulangi</button></form><?php endif; ?> <?php endif; ?></td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7" class="text-center text-muted py-5">Belum ada event integrasi.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-body"><?php echo e($events->links()); ?></div>
</div>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('audit.view')): ?>
<div class="card">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div><h5 class="mb-1">Audit Aktivitas</h5><small class="text-muted">Append-only, tenant-scoped, dan tidak menyimpan nilai sensitif.</small></div>
    <form method="GET" class="d-flex gap-2"><input class="form-control form-control-sm" name="audit_search" value="<?php echo e(request('audit_search')); ?>" placeholder="Route / request ID"><button class="btn btn-sm btn-label-primary">Cari</button></form>
  </div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Status</th><th>Field</th><th>Request ID</th></tr></thead>
      <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $audits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td class="small text-nowrap"><?php echo e($audit->occurred_at?->format('d/m/Y H:i:s')); ?></td>
          <td><?php echo e($audit->user?->name ?? 'System/Guest'); ?></td>
          <td><strong><?php echo e($audit->action); ?></strong><div class="small text-muted"><?php echo e($audit->method); ?> <?php echo e(Illuminate\Support\Str::limit($audit->path,50)); ?></div></td>
          <td><span class="badge bg-label-<?php echo e(($audit->response_status ?? 500) < 400 ? 'success' : 'danger'); ?>"><?php echo e($audit->response_status ?? '-'); ?></span></td>
          <td class="small"><?php echo e(collect($audit->changed_fields ?? [])->implode(', ') ?: '-'); ?></td>
          <td><code><?php echo e(Illuminate\Support\Str::limit($audit->request_id,13)); ?></code></td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="text-center text-muted py-5">Audit akan muncul setelah ada perubahan data.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-body"><?php echo e($audits->links()); ?></div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts/layoutMaster', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/setting/integrations/index.blade.php ENDPATH**/ ?>