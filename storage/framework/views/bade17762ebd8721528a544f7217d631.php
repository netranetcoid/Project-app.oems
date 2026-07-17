

<?php $__env->startSection('title', 'Data Pegawai'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>
                <h4 class="mb-0">
                    <i class="ti ti-users me-2"></i>
                    Data Pegawai
                </h4>

                <small class="text-muted">
                    Master Data Pegawai OEMS
                </small>
            </div>

            <a href="<?php echo e(route('employees.create')); ?>"
               class="btn btn-primary">

                <i class="ti ti-plus"></i>

                Tambah Pegawai

            </a>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                    <tr>

                        <th width="70">Foto</th>

                        <th>Kode</th>

                        <th>Nama</th>

                        <th>Branch</th>

                        <th>Divisi</th>

                        <th>Jabatan</th>

                        <th>Status</th>

                        <th width="140">Aksi</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>

                            <td>

                                <img src="<?php echo e($employee->photo_url); ?>"
                                     class="rounded-circle"
                                     width="45">

                            </td>

                            <td>

                                <?php echo e($employee->employee_no); ?>


                            </td>

                            <td>

                                <strong>

                                    <?php echo e($employee->name); ?>


                                </strong>

                                <br>

                                <small class="text-muted">

                                    <?php echo e($employee->email); ?>


                                </small>

                            </td>

                            <td>

                                <?php echo e($employee->branch?->name); ?>


                            </td>

                            <td>

                                <?php echo e($employee->division?->name); ?>


                            </td>

                            <td>

                                <?php echo e($employee->position?->name); ?>


                            </td>

                            <td>

                                <?php if($employee->work_status=='active'): ?>

                                    <span class="badge bg-success">

                                        Aktif

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-danger">

                                        Non Aktif

                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

    <div class="btn-group">

        <a href="<?php echo e(route('employees.show',$employee)); ?>"
           class="btn btn-sm btn-info"
           title="Detail Pegawai">

            <i class="ti ti-eye"></i>

        </a>

        <a href="<?php echo e(route('employees.edit',$employee)); ?>"
           class="btn btn-sm btn-warning"
           title="Edit">

            <i class="ti ti-edit"></i>

        </a>

        <form method="POST"
              action="<?php echo e(route('employees.destroy',$employee)); ?>"
              class="d-inline">

            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>

            <button
                type="submit"
                class="btn btn-sm btn-danger"
                onclick="return confirm('Hapus pegawai?')">

                <i class="ti ti-trash"></i>

            </button>

        </form>

    </div>

</td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>

                            <td colspan="8"
                                class="text-center">

                                Belum ada data pegawai.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div class="mt-3">

                <?php echo e($employees->links()); ?>


            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/master/employees/index.blade.php ENDPATH**/ ?>