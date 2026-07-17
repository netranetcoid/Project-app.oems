

<?php $__env->startSection('title', 'Master Shift'); ?>

<?php $__env->startSection('content'); ?>

<div class="row">

    <div class="col-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="card-title mb-1">

                        Master Shift

                    </h4>

                    <small class="text-muted">

                        Kelola seluruh shift kerja perusahaan

                    </small>

                </div>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attendance.shift.create')): ?>

                <a href="<?php echo e(route('attendance.shifts.create')); ?>"
                    class="btn btn-primary">

                    <i class="ti ti-plus me-1"></i>

                    Tambah Shift

                </a>

                <?php endif; ?>

            </div>

            <div class="card-body">

                <?php if(session('success')): ?>

                <div class="alert alert-success">

                    <?php echo e(session('success')); ?>


                </div>

                <?php endif; ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead>

                            <tr>

                                <th width="60">No</th>

                                <th>Kode</th>

                                <th>Nama Shift</th>

                                <th>Site</th>

                                <th>Jam Kerja</th>

                                <th>Status</th>

                                <th width="170">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>

                            <td>

                                <?php echo e($loop->iteration); ?>


                            </td>

                            <td>

                                <?php echo e($shift->code); ?>


                            </td>

                            <td>

                                <?php echo e($shift->name); ?>


                            </td>

                            <td>

                                <?php echo e($shift->branch?->name ?? 'Semua Site'); ?>


                            </td>

                            <td>

                                <?php echo e(substr($shift->clock_in_time,0,5)); ?>


                                -

                                <?php echo e(substr($shift->clock_out_time,0,5)); ?>


                            </td>

                            <td>

                                <?php if($shift->status=='active'): ?>

                                <span class="badge bg-label-success">

                                    Active

                                </span>

                                <?php else: ?>

                                <span class="badge bg-label-danger">

                                    Inactive

                                </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                                              <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attendance.shift.update')): ?>

                                <a href="<?php echo e(route('attendance.shifts.edit',$shift)); ?>"
                                    class="btn btn-sm btn-warning">

                                    <i class="ti ti-edit"></i>

                                </a>

                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attendance.shift.delete')): ?>

                                <form
                                    action="<?php echo e(route('attendance.shifts.destroy',$shift)); ?>"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus shift ini?')">

                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-danger">

                                        <i class="ti ti-trash"></i>

                                    </button>

                                </form>

                                <?php endif; ?>

                            </td>

                        </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>

                            <td colspan="7"
                                class="text-center text-muted">

                                Belum ada data Shift.

                            </td>

                        </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">

                    <?php echo e($shifts->links()); ?>


                </div>

            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/attendance/shifts/index.blade.php ENDPATH**/ ?>