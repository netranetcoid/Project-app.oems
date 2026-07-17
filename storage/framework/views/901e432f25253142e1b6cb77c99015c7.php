<?php
$containerNav = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid';
$navbarDetached = $navbarDetached ?? '';
?>

<!-- Navbar -->
<?php if(isset($navbarDetached) && $navbarDetached == 'navbar-detached'): ?>
<nav
  class="layout-navbar <?php echo e($containerNav); ?> <?php echo e($navbarDetached); ?> navbar navbar-expand-xl align-items-center bg-navbar-theme"
  id="layout-navbar">
  <?php echo $__env->make('layouts/sections/navbar/navbar-partial', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</nav>
<?php else: ?>
<nav class="layout-navbar navbar navbar-expand-xl align-items-center" id="layout-navbar">
  <div class="<?php echo e($containerNav); ?>"><?php echo $__env->make('layouts/sections/navbar/navbar-partial', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
</nav>
<?php endif; ?>
<!-- / Navbar --><?php /**PATH C:\laragon\www\appoems\resources\views/layouts/sections/navbar/navbar.blade.php ENDPATH**/ ?>