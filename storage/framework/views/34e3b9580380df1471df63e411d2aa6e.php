

<?php $__env->startSection('title','Kontrak Pegawai'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

<div class="card">

<div class="card-header d-flex justify-content-between">

<div>

<h4>

Kontrak Pegawai

</h4>

<small>

Riwayat Kontrak Pegawai

</small>

</div>

<a
href="<?php echo e(route('hr.contracts.create')); ?>"
class="btn btn-primary">

<i class="ti ti-plus"></i>

Tambah Kontrak

</a>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>No</th>

<th>No Kontrak</th>

<th>Pegawai</th>

<th>Jenis</th>

<th>Mulai</th>

<th>Selesai</th>

<th>Status</th>

<th></th>

</tr>

</thead>

<tbody>

<?php $__empty_1 = true; $__currentLoopData = $contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

<tr>

<td>

<?php echo e($loop->iteration); ?>


</td>

<td>

<strong>

<?php echo e($contract->contract_no); ?>


</strong>

</td>

<td>

<?php echo e($contract->employee_name); ?>


</td>

<td>

<?php echo e($contract->contractType?->name); ?>


</td>

<td>

<?php echo e(optional($contract->start_date)->format('d M Y')); ?>


</td>

<td>

<?php echo e(optional($contract->end_date)->format('d M Y')); ?>


</td>

<td>

<span class="badge bg-<?php echo e($contract->status_badge); ?>">

<?php echo e($contract->status_label); ?>


</span>

</td>

<td>

<div class="btn-group">

<a
href="<?php echo e(route('hr.contracts.show',$contract)); ?>"
class="btn btn-info btn-sm">

<i class="ti ti-eye"></i>

</a>

<a
href="<?php echo e(route('hr.contracts.edit',$contract)); ?>"
class="btn btn-warning btn-sm">

<i class="ti ti-edit"></i>

</a>

<a
href="<?php echo e(route('hr.contracts.pdf',$contract)); ?>"
class="btn btn-danger btn-sm"
target="_blank"
title="Cetak / simpan PDF">

<i class="ti ti-file-type-pdf"></i>

</a>

<a
href="<?php echo e(route('hr.contracts.print',$contract)); ?>"
class="btn btn-secondary btn-sm"
target="_blank"
title="Print preview">

<i class="ti ti-printer"></i>

</a>

</div>

</td>

</tr>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

<tr>

<td colspan="8"
class="text-center">

Belum ada kontrak.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

<?php echo e($contracts->links()); ?>


</div>

</div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/hr/contracts/index.blade.php ENDPATH**/ ?>