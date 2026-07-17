

<?php $__env->startSection('title', 'Master Division'); ?>

<?php $__env->startSection('content'); ?>

<div class="row">

    <div class="col-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="card-title mb-1">
                        Master Division
                    </h4>

                    <small class="text-muted">
                        Kelola seluruh Division perusahaan
                    </small>

                </div>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('division.create')): ?>

                <a href="<?php echo e(route('master.divisions.create')); ?>"
                    class="btn btn-primary">

                    <i class="ti ti-plus me-1"></i>

                    Tambah Division

                </a>

                <?php endif; ?>

            </div>

            <div class="card-body">

                <?php if(session('success')): ?>

                <div class="alert alert-success alert-dismissible fade show">

                    <?php echo e(session('success')); ?>


                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"></button>

                </div>

                <?php endif; ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead>

                            <tr>

                                <th width="60">No</th>
                                <th>Kode</th>
                                <th>Division</th>
                                <th>Parent</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th width="150">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php $__empty_1 = true; $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td>
                                    <?php echo e($loop->iteration + ($divisions->firstItem() - 1)); ?>

                                </td>

                                <td><?php echo e($division->code); ?></td>

                                <td><?php echo e($division->name); ?></td>

                                <td><?php echo e($division->parent?->name ?? '-'); ?></td>

                                <td><?php echo e($division->company?->name); ?></td>

                                <td>

                                    <?php if($division->status=='active'): ?>

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

                                    <div class="d-flex gap-1">

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('division.update')): ?>

                                        <a href="<?php echo e(route('master.divisions.edit',$division)); ?>"
                                            class="btn btn-sm btn-icon btn-label-warning">

                                            <i class="ti ti-edit"></i>

                                        </a>

                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('division.delete')): ?>

                                        <form
                                            action="<?php echo e(route('master.divisions.destroy',$division)); ?>"
                                            method="POST"
                                            onsubmit="return confirm('Hapus Division ini?')">

                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-icon btn-label-danger">

                                                <i class="ti ti-trash"></i>

                                            </button>

                                        </form>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>

                                <td colspan="7"
                                    class="text-center py-4">

                                    Belum ada data Division.

                                </td>

                            </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <?php if($divisions->hasPages()): ?>

                <div class="mt-3">

                    <?php echo e($divisions->links()); ?>


                </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/master/Divisions/index.blade.php ENDPATH**/ ?>