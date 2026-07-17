<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
  <div class="row g-4">
    <div class="col-12">
      <div class="card osm-dashboard-hero overflow-hidden">
        <div class="card-body position-relative p-4 p-lg-5">
          <div class="osm-hero-orbit" aria-hidden="true"></div>
          <div class="row align-items-center g-4 position-relative">
            <div class="col-lg-8">
              <div class="d-flex align-items-center gap-3 mb-3">
                <img src="<?php echo e(asset('assets/img/logo/osm-brand-mark-v2.png')); ?>" alt="OSM" class="osm-hero-logo">
                <div class="osm-hero-divider d-none d-sm-block"></div>
                <div class="d-none d-sm-block">
                  <div class="osm-hero-eyebrow">Enterprise Management System</div>
                  <div class="osm-hero-company"><?php echo e(config('variables.companyLegalName')); ?></div>
                </div>
              </div>
              <h2 class="text-white mb-2">Selamat datang, <?php echo e($stats['user_name']); ?></h2>
              <p class="osm-hero-copy mb-4">
                Satu pusat kendali untuk SDM, absensi, kontrak, payroll, KPI, dan operasional perusahaan.
              </p>

              <div class="d-flex flex-wrap gap-2 osm-quick-actions">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employees.view')): ?><a href="<?php echo e(route('employees.index')); ?>" class="btn btn-sm btn-light"><i class="ri ri-team-line me-1"></i>Pegawai</a><?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attendance.view')): ?><a href="<?php echo e(route('attendance.index')); ?>" class="btn btn-sm btn-outline-light"><i class="ri ri-fingerprint-line me-1"></i>Absensi</a><?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payroll.view')): ?><a href="<?php echo e(route('hr.payroll.index')); ?>" class="btn btn-sm btn-outline-light"><i class="ri ri-wallet-3-line me-1"></i>Payroll</a><?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employees.view')): ?><a href="<?php echo e(route('hr.contracts.index')); ?>" class="btn btn-sm btn-outline-light"><i class="ri ri-file-text-line me-1"></i>Kontrak</a><?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('hr-request.view')): ?><a href="<?php echo e(route('hr.requests.index')); ?>" class="btn btn-sm btn-outline-light"><i class="ri ri-file-check-line me-1"></i>Approval</a><?php endif; ?>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="osm-active-company-card ms-lg-auto">
                <span class="osm-status-pill"><span></span>Sistem aktif</span>
                <small>Perusahaan aktif</small>
                <strong><?php echo e($stats['company_name']); ?></strong>
                <div>Company ID <?php echo e($stats['active_company_id'] ?? '-'); ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-user"></i>
              </span>
            </div>
            <div>
              <p class="mb-0 text-muted">User Login</p>
              <h6 class="mb-0"><?php echo e($stats['user_name']); ?></h6>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-building"></i>
              </span>
            </div>
            <div>
              <p class="mb-0 text-muted">Perusahaan Aktif</p>
              <h6 class="mb-0"><?php echo e($stats['company_name']); ?></h6>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-shield-lock"></i>
              </span>
            </div>
            <div>
              <p class="mb-0 text-muted">Total Role</p>
              <h6 class="mb-0"><?php echo e($stats['roles']->count()); ?></h6>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti ti-key"></i>
              </span>
            </div>
            <div>
              <p class="mb-0 text-muted">Permission</p>
              <h6 class="mb-0"><?php echo e($stats['permissions_count']); ?></h6>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Akses Aktif</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <div class="fw-medium mb-2">Email</div>
            <span class="text-muted"><?php echo e($stats['user_email']); ?></span>
          </div>

          <div>
            <div class="fw-medium mb-2">Role pada perusahaan aktif</div>

            <?php $__empty_1 = true; $__currentLoopData = $stats['roles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <span class="badge bg-label-primary me-1 mb-1"><?php echo e($role); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <div class="alert alert-warning mb-0">
                User ini belum punya role pada company aktif.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/layoutMaster', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/dashboard/dashboard_index.blade.php ENDPATH**/ ?>