<?php
  // Logo OSM berbentuk lebar. Width/height dipakai sebagai batas, bukan
  // dipaksakan menjadi kotak, supaya proporsi huruf dan orbit tidak gepeng.
  $logoWidth = $width ?? (($height ?? 32) * 2.35);
  $logoHeight = $height ?? null;
?>

<span class="d-inline-flex align-items-center justify-content-center flex-shrink-0 osm-logo-wrap">
  <img src="<?php echo e(asset('assets/img/logo/osm-brand-mark-v2.png')); ?>"
    width="<?php echo e($logoWidth); ?>" <?php if($logoHeight): ?> height="<?php echo e($logoHeight); ?>" <?php endif; ?>
    alt="OSM" class="osm-logo-image" loading="eager">
</span>
<?php /**PATH C:\laragon\www\appoems\resources\views/_partials/macros.blade.php ENDPATH**/ ?>