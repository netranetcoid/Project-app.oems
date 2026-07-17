<ul class="menu-sub">
  <?php $__currentLoopData = $menu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submenu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $hasSubmenu = isset($submenu->submenu) && count($submenu->submenu) > 0;
      $activeClass = $isActiveMenu($submenu) ? ($hasSubmenu ? 'active open' : 'active') : '';
      $target = !empty($submenu->target) ? $submenu->target : null;
    ?>

    <li class="menu-item <?php echo e($activeClass); ?>">
      <a href="<?php echo e($hasSubmenu ? 'javascript:void(0);' : $menuUrl($submenu)); ?>"
        class="menu-link <?php echo e($hasSubmenu ? 'menu-toggle' : ''); ?>"
        <?php if($target): ?> target="<?php echo e($target); ?>" <?php endif; ?>>

        <?php if(!empty($submenu->icon)): ?>
          <i class="<?php echo e($submenu->icon); ?>"></i>
        <?php endif; ?>

        <div><?php echo e(__($submenu->name ?? ($submenu->label ?? '-'))); ?></div>

        <?php if(!empty($submenu->badge_text)): ?>
          <div class="badge bg-label-<?php echo e($submenu->badge_color ?? 'primary'); ?> rounded-pill ms-auto">
            <?php echo e($submenu->badge_text); ?>

          </div>
        <?php endif; ?>
      </a>

      <?php if($hasSubmenu): ?>
        <?php echo $__env->make('layouts.sections.menu.submenu', [
            'menu' => $submenu->submenu,
            'isActiveMenu' => $isActiveMenu,
            'menuUrl' => $menuUrl,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <?php endif; ?>
    </li>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH C:\laragon\www\appoems\resources\views/layouts/sections/menu/submenu.blade.php ENDPATH**/ ?>