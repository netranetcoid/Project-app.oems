<?php
  use App\Services\MenuService;
  use Illuminate\Support\Facades\Route;

  $configData = Helper::appClasses();
  $currentRouteName = Route::currentRouteName() ?? '';
  $user = auth()->user();
  $companyId = session('company_id');

  if ($companyId && function_exists('setPermissionsTeamId')) {
      setPermissionsTeamId((int) $companyId);
  }

  $isFalseLike = function ($value) {
      return in_array($value, [false, 0, '0', 'false', 'inactive', 'no'], true);
  };

  $canSeeSelf = function ($item) use ($user, $isFalseLike) {
      if (!$user) {
          return false;
      }

      if (isset($item->is_active) && $isFalseLike($item->is_active)) {
          return false;
      }

      if (isset($item->is_visible) && $isFalseLike($item->is_visible)) {
          return false;
      }

      $permission = trim((string) ($item->permission_name ?? ($item->permission ?? '')));

      if ($permission === '') {
          return true;
      }

      return $user->can($permission);
  };

  $filterMenuTree = function ($items) use (&$filterMenuTree, $canSeeSelf) {
      return collect($items ?? [])
          ->map(function ($item) use (&$filterMenuTree) {
              if (isset($item->submenu) && is_iterable($item->submenu)) {
                  $item->submenu = $filterMenuTree($item->submenu)->values();
              }

              return $item;
          })
          ->filter(function ($item) use ($canSeeSelf) {
              $hasVisibleChildren = isset($item->submenu) && count($item->submenu) > 0;

              return $canSeeSelf($item) || $hasVisibleChildren;
          })
          ->values();
  };

  $isActiveMenu = function ($menu) use (&$isActiveMenu, $currentRouteName) {
      if (empty($currentRouteName)) {
          return false;
      }

      $slug = $menu->slug ?? ($menu->code ?? ($menu->route_name ?? null));
      $routeName = $menu->route_name ?? null;

      if ($routeName && $routeName === $currentRouteName) {
          return true;
      }

      if ($slug && $slug === $currentRouteName) {
          return true;
      }

      if ($slug && str_starts_with($currentRouteName, $slug)) {
          return true;
      }

      if (isset($menu->submenu) && is_iterable($menu->submenu)) {
          foreach ($menu->submenu as $child) {
              if ($isActiveMenu($child)) {
                  return true;
              }
          }
      }

      return false;
  };

  $menuUrl = function ($menu) {
      if (!empty($menu->url)) {
          return url(ltrim($menu->url, '/'));
      }

      if (!empty($menu->route_name) && Route::has($menu->route_name)) {
          return route($menu->route_name);
      }

      return 'javascript:void(0);';
  };

  /*
  |--------------------------------------------------------------------------
  | PENTING
  |--------------------------------------------------------------------------
  | Jangan pakai $menuData lagi di sini.
  | Ambil langsung dari database via MenuService supaya tidak ketimpa JSON provider.
  */
  $menus = $filterMenuTree(app(MenuService::class)->getMenus());
?>

<aside id="layout-menu" class="layout-menu menu-vertical menu"
  <?php $__currentLoopData = $configData['menuAttributes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php echo e($attribute); ?>="<?php echo e($value); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>

  <div class="app-brand demo osm-sidebar-brand">
    <a href="<?php echo e(url('/')); ?>" class="app-brand-link osm-brand-link" aria-label="Dashboard OSM">
      <span class="app-brand-logo demo"><?php echo $__env->make('_partials.macros', ['width' => 58], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
      <span class="osm-brand-copy menu-text">
        <strong>OSM Enterprise</strong>
        <small><?php echo e(config('variables.companyLegalName')); ?></small>
      </span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="icon-base ri ri-menu-fold-line icon-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <div class="osm-company-context menu-text">
    <span class="osm-context-dot"></span>
    <span>
      <small>Perusahaan aktif</small>
      <strong><?php echo e(session('company_name') ?: config('variables.companyLegalName')); ?></strong>
    </span>
  </div>

  <ul class="menu-inner py-1">
    <?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $hasSubmenu = isset($menu->submenu) && count($menu->submenu) > 0;
        $activeClass = $isActiveMenu($menu) ? ($hasSubmenu ? 'active open' : 'active') : '';
        $target = !empty($menu->target) ? $menu->target : null;
      ?>

      <li class="menu-item <?php echo e($activeClass); ?>">
        <a href="<?php echo e($hasSubmenu ? 'javascript:void(0);' : $menuUrl($menu)); ?>"
          class="menu-link <?php echo e($hasSubmenu ? 'menu-toggle' : ''); ?>"
          <?php if($target): ?> target="<?php echo e($target); ?>" <?php endif; ?>>

          <?php if(!empty($menu->icon)): ?>
            <i class="<?php echo e($menu->icon); ?>"></i>
          <?php else: ?>
            <i class="menu-icon tf-icons ri ri-circle-line icon-18px"></i>
          <?php endif; ?>

          <div><?php echo e(__($menu->name ?? ($menu->label ?? '-'))); ?></div>

          <?php if(!empty($menu->badge_text)): ?>
            <div class="badge bg-label-<?php echo e($menu->badge_color ?? 'primary'); ?> rounded-pill ms-auto">
              <?php echo e($menu->badge_text); ?>

            </div>
          <?php endif; ?>
        </a>

        <?php if($hasSubmenu): ?>
          <?php echo $__env->make('layouts.sections.menu.submenu', [
              'menu' => $menu->submenu,
              'isActiveMenu' => $isActiveMenu,
              'menuUrl' => $menuUrl,
          ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
      </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </ul>
</aside>
<?php /**PATH C:\laragon\www\appoems\resources\views/layouts/sections/menu/verticalMenu.blade.php ENDPATH**/ ?>