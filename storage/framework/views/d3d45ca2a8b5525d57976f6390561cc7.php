

<?php $__env->startSection('title', 'Master Site'); ?>

<?php $__env->startSection('content'); ?>

<div class="row">

    <div class="col-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="card-title mb-1">
                        Master Site
                    </h4>

                    <small class="text-muted">
                        Kelola seluruh Site perusahaan
                    </small>

                </div>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('branch.create')): ?>

                <a href="<?php echo e(route('master.branches.create')); ?>"
                    class="btn btn-primary">

                    <i class="ti ti-plus me-1"></i>

                    Tambah Site

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

                                <th>Nama Site</th>

                                <th>Company</th>

                                <th>Status</th>

                                <th width="150">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php $__empty_1 = true; $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td>
                                    <?php echo e($loop->iteration + ($branches->firstItem() - 1)); ?>

                                </td>

                                <td>
                                    <?php echo e($branch->code); ?>

                                </td>

                                <td>
                                    <?php echo e($branch->name); ?>

                                </td>

                                <td>
                                    <?php echo e($branch->company?->name); ?>

                                </td>

                                <td>

                                    <?php switch($branch->status):

                                    case ('active'): ?>

                                    <span class="badge bg-label-success">
                                        Active
                                    </span>

                                    <?php break; ?>

                                    <?php case ('inactive'): ?>

                                    <span class="badge bg-label-warning">
                                        Inactive
                                    </span>

                                    <?php break; ?>

                                    <?php default: ?>

                                    <span class="badge bg-label-danger">
                                        Closed
                                    </span>

                                    <?php endswitch; ?>

                                </td>

                                <td>

                                    <div class="d-flex gap-1">

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('branch.update')): ?>

                                        <a href="<?php echo e(route('master.branches.edit',$branch)); ?>"
                                            class="btn btn-sm btn-icon btn-label-warning">

                                            <i class="ti ti-edit"></i>

                                        </a>

                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('branch.delete')): ?>

                                        <form action="<?php echo e(route('master.branches.destroy',$branch)); ?>"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus Site ini?')">

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

                                <td colspan="6"
                                    class="text-center py-4 text-muted">

                                    Belum ada data Site.

                                </td>

                            </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <?php if($branches->hasPages()): ?>

                <div class="mt-3">

                    <?php echo e($branches->links()); ?>


                </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/master/Branches/index.blade.php ENDPATH**/ ?>