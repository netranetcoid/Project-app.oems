

<?php $__env->startSection('title','Detail Kontrak'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="mb-1">Detail Kontrak</h4>
            <span class="text-muted"><?php echo e($contract->contract_no); ?></span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo e(route('hr.contracts.index')); ?>" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>Kembali
            </a>
            <a href="<?php echo e(route('hr.contracts.print', $contract)); ?>" target="_blank" class="btn btn-outline-primary">
                <i class="ti ti-printer me-1"></i>Preview Cetak
            </a>
            <a href="<?php echo e(route('hr.contracts.pdf', $contract)); ?>" target="_blank" class="btn btn-danger">
                <i class="ti ti-file-type-pdf me-1"></i>Cetak PDF
            </a>
        </div>
    </div>

    <div class="row">

        
        <div class="col-xl-4">

            <div class="card">

                <div class="card-body text-center">

                   <?php if($contract->employee && $contract->employee->photo): ?>

<img
    src="<?php echo e(asset('storage/'.$contract->employee->photo)); ?>"
    class="rounded-circle mb-3"
    width="120"
    height="120"
    style="object-fit:cover;">

<?php else: ?>

<img
    src="<?php echo e(asset('assets/img/avatars/1.png')); ?>"
    class="rounded-circle mb-3"
    width="120"
    height="120"
    style="object-fit:cover;">

<?php endif; ?>

                    <h4>

                        <?php echo e($contract->employee_name); ?>


                    </h4>

                    <p class="text-muted">

                        <?php echo e($contract->position_name); ?>


                    </p>

                    <span class="badge bg-<?php echo e($contract->status_badge); ?>">

                        <?php echo e($contract->status_label); ?>


                    </span>

                    <hr>

                    <div class="text-start">

                        <p>

                            <strong>No Kontrak</strong><br>

                            <?php echo e($contract->contract_no); ?>


                        </p>

                        <p>

                            <strong>Jenis</strong><br>

                            <?php echo e($contract->contractType?->name); ?>


                        </p>

                        <p>

                            <strong>Mulai</strong><br>

                            <?php echo e(optional($contract->start_date)->format('d M Y')); ?>


                        </p>

                        <p>

                            <strong>Berakhir</strong><br>

                            <?php if($contract->contractType?->is_probation): ?>
                                PKWTT - tidak berakhir otomatis
                            <?php else: ?>
                                <?php echo e(optional($contract->end_date)->format('d M Y')); ?>

                            <?php endif; ?>

                        </p>

                        <?php if($contract->contractType?->is_probation): ?>
                        <p>
                            <strong>Akhir Evaluasi Probation</strong><br>
                            <?php echo e(optional($contract->probation_end_date)->format('d M Y')); ?>

                        </p>
                        <?php endif; ?>

                        <p>

                            <strong>Sisa Hari</strong><br>

                            <?php echo e($contract->remaining_days); ?>


                        </p>

                    </div>

                </div>

            </div>

        </div>

        
        <div class="col-xl-8">

            <div class="card">

                <div class="card-header">

                    <h5>

                        Informasi Kontrak

                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Nomor Surat

                            </label>

                            <div>

                                <?php echo e($contract->letter_no ?? '-'); ?>


                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Lama Kontrak

                            </label>

                            <div>

                                <?php echo e($contract->duration_month); ?> Bulan

                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Site

                            </label>

                            <div>

                                <?php echo e($contract->branch_name); ?>


                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Divisi

                            </label>

                            <div>

                                <?php echo e($contract->division_name); ?>


                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Jabatan

                            </label>

                            <div>

                                <?php echo e($contract->position_name); ?>


                            </div>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">

                                Gaji Pokok

                            </label>

                            <div>

                                Rp <?php echo e(number_format($contract->basic_salary,0,',','.')); ?>


                            </div>

                        </div>

                    </div>

                    <hr>

                    <h6>

                        Catatan

                    </h6>

                    <?php echo e($contract->notes ?: '-'); ?>


                </div>

            </div>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/hr/contracts/show.blade.php ENDPATH**/ ?>