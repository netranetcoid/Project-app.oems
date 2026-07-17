

<?php $__env->startSection('title', 'Master Jenis Kontrak'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    
    <div class="row mb-4">

        <div class="col-md-4">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Total Jenis Kontrak
                            </small>

                            <h3 class="mb-0">
                                <?php echo e($stats['total']); ?>

                            </h3>

                        </div>

                        <div class="avatar">

                            <span class="avatar-initial rounded bg-primary">

                                <i class="ti ti-file-description"></i>

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Aktif
                            </small>

                            <h3 class="text-success mb-0">

                                <?php echo e($stats['active']); ?>


                            </h3>

                        </div>

                        <div class="avatar">

                            <span class="avatar-initial rounded bg-success">

                                <i class="ti ti-check"></i>

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Non Aktif
                            </small>

                            <h3 class="text-danger mb-0">

                                <?php echo e($stats['inactive']); ?>


                            </h3>

                        </div>

                        <div class="avatar">

                            <span class="avatar-initial rounded bg-danger">

                                <i class="ti ti-x"></i>

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    
    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>

                <h4 class="mb-0">

                    Master Jenis Kontrak

                </h4>

                <small class="text-muted">

                    Digunakan oleh Modul Kontrak Pegawai

                </small>

            </div>

            <a href="<?php echo e(route('master.contract-types.create')); ?>"
               class="btn btn-primary">

                <i class="ti ti-plus"></i>

                Tambah

            </a>

        </div>

        <div class="card-body">

            <form method="GET"
                  class="row mb-3">

                <div class="col-md-4">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari kode / nama..."
                        value="<?php echo e(request('search')); ?>">

                </div>

                <div class="col-md-auto">

                    <button
                        class="btn btn-outline-primary">

                        Cari

                    </button>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                    <tr>

                        <th>Kode</th>

                        <th>Nama</th>

                        <th>Durasi</th>

                        <th>Status</th>

                        <th width="130">Aksi</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $contractTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>

                            <td>

                                <?php echo e($item->code); ?>


                            </td>

                            <td>

                                <strong>

                                    <?php echo e($item->name); ?>


                                </strong>

                            </td>

                            <td>

                                <?php echo e($item->duration_label); ?>


                            </td>

                            <td>

                                <span class="badge bg-<?php echo e($item->status_badge); ?>">

                                    <?php echo e($item->status_label); ?>


                                </span>

                            </td>

                            <td>

                                <a href="<?php echo e(route('master.contract-types.edit',$item)); ?>"
                                   class="btn btn-warning btn-sm">

                                    <i class="ti ti-edit"></i>

                                </a>

                                <form
                                    action="<?php echo e(route('master.contract-types.destroy',$item)); ?>"
                                    method="POST"
                                    class="d-inline">

                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus jenis kontrak?')">

                                        <i class="ti ti-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>

                            <td colspan="5"
                                class="text-center">

                                Belum ada data.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div class="mt-3">

                <?php echo e($contractTypes->links()); ?>


            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/master/contract-types/index.blade.php ENDPATH**/ ?>