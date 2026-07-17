<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap" rel="stylesheet" />

<!-- Fonts Icons -->
<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/fonts/iconify/iconify.css']); ?>

<!-- BEGIN: Vendor CSS-->
<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/libs/node-waves/node-waves.scss']); ?>

<?php if($configData['hasCustomizer']): ?>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/libs/pickr/pickr-themes.scss']); ?>
<?php endif; ?>

<!-- Core CSS -->
<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/scss/core.scss', 'resources/assets/css/demo.css', 'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss']); ?>

<!-- Vendor Styles -->
<?php echo app('Illuminate\Foundation\Vite')(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss', 'resources/assets/vendor/libs/typeahead-js/typeahead.scss']); ?>
<?php echo $__env->yieldContent('vendor-style'); ?>

<!-- Page Styles -->
<?php echo $__env->yieldContent('page-style'); ?>

<!-- OSM custom CSS sengaja dimuat langsung dari source agar perubahan branding
     langsung berlaku di Laragon tanpa mewajibkan npm/vite build pada PC HR. -->
<style id="osm-app-styles">
  <?php echo file_get_contents(resource_path('css/app.css')); ?>

</style>
<!-- END: app CSS-->
<?php /**PATH C:\laragon\www\appoems\resources\views/layouts/sections/styles.blade.php ENDPATH**/ ?>