<?php $__env->startSection('title', 'Aturan HR & Absensi'); ?>
<?php $__env->startSection('content'); ?>
<?php ($settings = is_array($company->settings) ? $company->settings : []); ?>
<div class="mb-4"><h4 class="mb-1">Aturan HR, Absensi & Payroll</h4><p class="text-muted mb-0">Master kebijakan PT OSM. Site dapat memiliki koordinat/radius sendiri melalui Master Site.</p></div>
<?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
<?php if($errors->any()): ?><div class="alert alert-danger"><ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>
<form method="POST" action="<?php echo e(route('hr.settings.update')); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
<div class="row">
  <div class="col-xl-7 mb-4"><div class="card h-100"><div class="card-header"><h5 class="mb-0">Absensi & Privasi</h5></div><div class="card-body row g-3">
    <div class="col-md-6"><label class="form-label">Latitude kantor utama</label><input class="form-control" name="office_latitude" required value="<?php echo e(old('office_latitude',$settings['office_latitude'] ?? -6.612755088971767)); ?>"></div>
    <div class="col-md-6"><label class="form-label">Longitude kantor utama</label><input class="form-control" name="office_longitude" required value="<?php echo e(old('office_longitude',$settings['office_longitude'] ?? 106.75548743646192)); ?>"></div>
    <div class="col-md-6"><label class="form-label">Radius default (meter)</label><input class="form-control" type="number" min="1" max="5000" name="attendance_radius_meter" required value="<?php echo e(old('attendance_radius_meter',$company->attendance_radius_meter ?? 150)); ?>"></div>
    <div class="col-md-6"><label class="form-label">Retention bukti (hari)</label><input class="form-control" type="number" min="1" max="3650" name="attendance_retention_days" required value="<?php echo e(old('attendance_retention_days',$settings['attendance_retention_days'] ?? 60)); ?>"></div>
    <div class="col-12 d-flex gap-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="attendance_gps_required" value="1" id="gps" <?php if(old('attendance_gps_required',$company->attendance_gps_required ?? true)): echo 'checked'; endif; ?>><label class="form-check-label" for="gps">GPS wajib</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="attendance_selfie_required" value="1" id="selfie" <?php if(old('attendance_selfie_required',$settings['attendance_selfie_required'] ?? true)): echo 'checked'; endif; ?>><label class="form-check-label" for="selfie">Selfie wajib</label></div></div>
  </div></div></div>
  <div class="col-xl-5 mb-4"><div class="card h-100"><div class="card-header"><h5 class="mb-0">Payroll</h5></div><div class="card-body row g-3">
    <div class="col-md-6"><label class="form-label">Tanggal gaji</label><input class="form-control" type="number" min="1" max="31" name="salary_payment_day" required value="<?php echo e(old('salary_payment_day',$company->salary_payment_day ?? 31)); ?>"><div class="form-text">Akhir bulan menyesuaikan jumlah hari.</div></div>
    <div class="col-md-6"><label class="form-label">Mata uang</label><input class="form-control text-uppercase" maxlength="3" name="default_currency" required value="<?php echo e(old('default_currency',$company->default_currency ?? 'IDR')); ?>"></div>
    <div class="col-12"><div class="alert alert-info mb-0">Bonus KPI tetap dijadwalkan tanggal 15 bulan berikutnya dari modul KPI/payroll.</div></div>
  </div></div></div>
</div>
<?php if(auth()->user()->can('attendance.update') || auth()->user()->can('payroll.update')): ?><button class="btn btn-primary">Simpan Aturan</button><?php endif; ?>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/hr/settings/index.blade.php ENDPATH**/ ?>