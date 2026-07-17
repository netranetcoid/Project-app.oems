

<?php $__env->startSection('title', 'Edit Jenis Kontrak'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <div class="card">

        <div class="card-header">

            <h4 class="mb-0">

                <i class="ti ti-edit me-2"></i>

                Edit Jenis Kontrak

            </h4>

        </div>

        <div class="card-body">

            <form
                method="POST"
                action="<?php echo e(route('master.contract-types.update',$contractType)); ?>">

                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <?php echo $__env->make('master.contract-types._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            </form>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\appoems\resources\views/master/contract-types/edit.blade.php ENDPATH**/ ?>