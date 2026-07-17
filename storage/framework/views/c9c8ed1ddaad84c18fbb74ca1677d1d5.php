<!-- BEGIN: Vendor JS-->

<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/popper/popper.js', 'resources/assets/vendor/js/bootstrap.js', 'resources/assets/vendor/libs/node-waves/node-waves.js', 'resources/assets/vendor/libs/@algolia/autocomplete-js.js']); ?>

<?php if($configData['hasCustomizer']): ?>
  <?php echo app('Illuminate\Foundation\Vite')('resources/assets/vendor/libs/pickr/pickr.js'); ?>
<?php endif; ?>

<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/libs/hammer/hammer.js', 'resources/assets/vendor/js/menu.js']); ?>

<?php echo $__env->yieldContent('vendor-script'); ?>
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/js/main.js']); ?>
<!-- END: Theme JS-->

<!-- Pricing Modal JS-->
<?php echo $__env->yieldPushContent('pricing-script'); ?>
<!-- END: Pricing Modal JS-->

<!-- BEGIN: Page JS-->
<?php echo $__env->yieldContent('page-script'); ?>
<!-- END: Page JS-->

<!-- app JS -->
<?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
<!-- END: app JS-->
<?php /**PATH C:\laragon\www\appoems\resources\views/layouts/sections/scripts.blade.php ENDPATH**/ ?>