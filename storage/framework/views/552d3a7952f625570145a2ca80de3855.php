<?php $__env->startSection('title', 'Payroll'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid"><div class="d-flex flex-wrap justify-content-between gap-3 align-items-end mb-4">
  <div><h4 class="mb-1">Payroll & Slip Gaji</h4><p class="text-muted mb-0">Gaji dibayar akhir bulan; bonus KPI tanggal 15 bulan berikutnya.</p></div>
  <form method="POST" action="<?php echo e(route('hr.payroll.generate')); ?>" class="d-flex gap-2 align-items-end"><?php echo csrf_field(); ?>
    <div><label class="form-label">Tahun</label><input class="form-control" type="number" name="period_year" value="<?php echo e(now()->year); ?>" min="2020" max="2100"></div>
    <div><label class="form-label">Bulan</label><input class="form-control" type="number" name="period_month" value="<?php echo e(now()->month); ?>" min="1" max="12"></div>
    <button class="btn btn-primary">Buat / hitung draft</button>
  </form>
</div>
<?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
<?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>
<div class="card"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Periode</th><th>Jadwal Bayar</th><th>Slip</th><th>Total Neto</th><th>Bonus KPI</th><th>Status</th><th></th></tr></thead><tbody>
<?php $__empty_1 = true; $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><strong><?php echo e(sprintf('%02d/%04d',$period->period_month,$period->period_year)); ?></strong></td><td>Gaji: <?php echo e($period->salary_payment_date->format('d/m/Y')); ?><br><span class="small text-muted">KPI: <?php echo e($period->kpi_payment_date->format('d/m/Y')); ?></span></td><td><?php echo e($period->slips_count); ?></td><td>Rp <?php echo e(number_format((float)$period->total_net,0,',','.')); ?></td><td>Rp <?php echo e(number_format((float)$period->total_kpi_bonus,0,',','.')); ?></td><td><span class="badge bg-label-<?php echo e($period->status === 'published' ? 'success' : ($period->status === 'approved' ? 'info' : 'warning')); ?>"><?php echo e(ucfirst($period->status)); ?></span></td><td><a class="btn btn-sm btn-label-primary" href="<?php echo e(route('hr.payroll.show',$period)); ?>">Detail</a></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7" class="text-center py-5 text-muted">Belum ada periode payroll.</td></tr><?php endif; ?>
</tbody></table></div><div class="card-body"><?php echo e($periods->links()); ?></div></div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/hr/payroll/index.blade.php ENDPATH**/ ?>