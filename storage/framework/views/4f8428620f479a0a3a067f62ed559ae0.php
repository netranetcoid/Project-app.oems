<?php $__env->startSection('title', 'Pengajuan HR'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><div><h4 class="mb-1">Pusat Pengajuan & Approval</h4><p class="text-muted mb-0">Cuti, sakit, izin, kasbon, piutang, lembur, dan klaim biaya dalam satu halaman.</p></div><?php if(!empty($activeTypes)): ?><div><span class="badge bg-label-primary">Filter: <?php echo e(implode(', ', $activeTypes)); ?></span></div><?php endif; ?></div>

  
  <div class="nav-align-top mb-4">
    <div class="nav nav-pills flex-wrap gap-2 osm-request-filter" role="navigation" aria-label="Filter pengajuan HR">
      <a class="nav-link <?php echo e(empty($activeTypes) ? 'active' : ''); ?>" href="<?php echo e(route('hr.requests.index')); ?>">Semua</a>
      <a class="nav-link <?php echo e(request()->routeIs('hr.requests.leave') ? 'active' : ''); ?>" href="<?php echo e(route('hr.requests.leave')); ?>">Cuti</a>
      <a class="nav-link <?php echo e(request()->routeIs('hr.requests.permission-sick') ? 'active' : ''); ?>" href="<?php echo e(route('hr.requests.permission-sick')); ?>">Izin & Sakit</a>
      <a class="nav-link <?php echo e(request()->routeIs('hr.requests.overtime') ? 'active' : ''); ?>" href="<?php echo e(route('hr.requests.overtime')); ?>">Lembur</a>
      <a class="nav-link <?php echo e(request()->routeIs('hr.requests.finance') ? 'active' : ''); ?>" href="<?php echo e(route('hr.requests.finance')); ?>">Kasbon, Piutang & Klaim</a>
    </div>
  </div>
  <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
  <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

  <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Master Kebijakan (dapat diedit HR)</h5></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Jenis</th><th>Maks. nominal</th><th>Maks. hari</th><th>Maks. cicilan</th><th>Dokumen</th><th>Aktif</th><th></th></tr></thead><tbody>
    <?php $__currentLoopData = $policies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $policy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><form method="POST" action="<?php echo e(route('hr.requests.policies.update', $policy)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
      <td><strong><?php echo e($policy->name); ?></strong><div class="small text-muted"><?php echo e($policy->type); ?></div></td>
      <td><input class="form-control form-control-sm" type="number" min="0" name="max_amount" value="<?php echo e($policy->max_amount); ?>" placeholder="Tanpa batas"></td>
      <td><input class="form-control form-control-sm" type="number" min="1" name="max_days" value="<?php echo e($policy->max_days); ?>" placeholder="-"></td>
      <td><input class="form-control form-control-sm" type="number" min="1" name="max_installments" value="<?php echo e($policy->max_installments); ?>" placeholder="-"></td>
      <td><input type="hidden" name="requires_document" value="0"><input class="form-check-input" type="checkbox" name="requires_document" value="1" <?php if($policy->requires_document): echo 'checked'; endif; ?>></td>
      <td><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?php if($policy->is_active): echo 'checked'; endif; ?>></td>
      <td><button class="btn btn-sm btn-label-primary">Simpan</button></td>
    </form></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody></table></div>
  </div>

  <div class="card"><div class="card-header"><h5 class="mb-0">Antrean Approval</h5></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>No / tanggal</th><th>Karyawan</th><th>Jenis</th><th>Rincian</th><th>Status</th><th>Keputusan HR</th></tr></thead><tbody>
    <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr>
      <td><strong><?php echo e($item->request_no); ?></strong><div class="small text-muted"><?php echo e($item->submitted_at?->format('d/m/Y H:i')); ?></div></td>
      <td><?php echo e($item->employee?->name); ?><div class="small text-muted"><?php echo e($item->branch?->name); ?></div></td>
      <td><?php echo e(ucwords(str_replace('_',' ', $item->type))); ?></td>
      <td><?php if($item->requested_amount): ?>Rp <?php echo e(number_format((float)$item->requested_amount,0,',','.')); ?> / <?php echo e($item->installment_count ?: 1); ?>x<br><?php endif; ?><?php echo e($item->start_date?->format('d/m/Y')); ?> <?php if($item->end_date): ?>–<?php echo e($item->end_date->format('d/m/Y')); ?><?php endif; ?><div class="small text-muted text-wrap" style="max-width:280px"><?php echo e($item->reason); ?></div></td>
      <td><span class="badge bg-label-<?php echo e($item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'warning')); ?>"><?php echo e(ucfirst($item->status)); ?></span></td>
      <td><?php if($item->status === 'submitted'): ?>
        <form method="POST" action="<?php echo e(route('hr.requests.approve',$item)); ?>" class="d-flex flex-column gap-1 mb-2"><?php echo csrf_field(); ?>
          <?php if(in_array($item->type,['cash_advance','receivable','reimbursement'])): ?><input class="form-control form-control-sm" name="approved_amount" type="number" min="1" value="<?php echo e($item->requested_amount); ?>" placeholder="Nominal disetujui"><?php endif; ?>
          <?php if(in_array($item->type,['cash_advance','receivable'])): ?><input class="form-control form-control-sm" name="installment_count" type="number" min="1" value="<?php echo e($item->installment_count ?: 1); ?>" placeholder="Cicilan"><?php endif; ?>
          <input class="form-control form-control-sm" name="hr_note" placeholder="Catatan HR"><button class="btn btn-sm btn-success">Setujui</button>
        </form>
        <form method="POST" action="<?php echo e(route('hr.requests.reject',$item)); ?>" class="d-flex gap-1"><?php echo csrf_field(); ?><input class="form-control form-control-sm" required minlength="5" name="hr_note" placeholder="Alasan penolakan"><button class="btn btn-sm btn-outline-danger">Tolak</button></form>
      <?php else: ?><div class="small text-muted"><?php echo e($item->hr_note ?: '-'); ?></div><?php endif; ?></td>
    </tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="text-center py-5 text-muted">Belum ada pengajuan.</td></tr><?php endif; ?>
    </tbody></table></div><div class="card-body"><?php echo e($requests->links()); ?></div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/hr/requests/index.blade.php ENDPATH**/ ?>