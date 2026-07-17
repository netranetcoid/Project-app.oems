@dd('HORIZONTAL MENU FILE INI KELOAD', __FILE__)
@php
  use Illuminate\Support\Facades\Route;

  $configData = Helper::appClasses();
  $currentRouteName = Route::currentRouteName() ?? '';
  $companyId = session('company_id');
  $user = auth()->user();

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

      $permission = $item->permission_name ?? ($item->permission ?? null);

      if (empty($permission)) {
          return true;
      }

      if (is_array($permission)) {
          foreach ($permission as $perm) {
              if ($user->can($perm)) {
                  return true;
              }
          }

          return false;
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

  $getActiveClass = function ($menu) use ($currentRouteName) {
      if (empty($currentRouteName)) {
          return null;
      }

      if (($menu->slug ?? null) === $currentRouteName) {
          return 'active';
      }

      $slugs = $menu->slug ?? [];

      if (is_array($slugs)) {
          foreach ($slugs as $slug) {
              if (!empty($slug) && str_starts_with($currentRouteName, $slug)) {
                  return 'active';
              }
          }
      } elseif (!empty($slugs) && str_starts_with($currentRouteName, $slugs)) {
          return 'active';
      }

      if (isset($menu->submenu) && is_iterable($menu->submenu)) {
          foreach ($menu->submenu as $child) {
              $childActive = null;

              if (($child->slug ?? null) === $currentRouteName) {
                  $childActive = 'active';
              } elseif (
                  !empty($child->slug) &&
                  is_string($child->slug) &&
                  str_starts_with($currentRouteName, $child->slug)
              ) {
                  $childActive = 'active';
              }

              if ($childActive) {
                  return 'active';
              }
          }
      }

      return null;
  };

  $menus = $filterMenuTree($menuData[1]->menu ?? []);
@endphp

<!-- Horizontal Menu -->
<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu flex-grow-0"
  @foreach ($configData['menuAttributes'] as $attribute => $value)
    {{ $attribute }}="{{ $value }}" @endforeach>

  <div class="{{ $containerNav }} d-flex h-100">
    <ul class="menu-inner">
      @foreach ($menus as $menu)
        @php
          $activeClass = $getActiveClass($menu);
          $hasSubmenu = isset($menu->submenu) && count($menu->submenu);
        @endphp

        <li class="menu-item {{ $activeClass }}">
          <a href="{{ isset($menu->url) && !$hasSubmenu ? url($menu->url) : 'javascript:void(0);' }}"
            class="{{ $hasSubmenu ? 'menu-link menu-toggle' : 'menu-link' }}"
            @if (isset($menu->target) && !empty($menu->target)) target="_blank" @endif>

            @isset($menu->icon)
              <i class="{{ $menu->icon }}"></i>
            @endisset

            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
          </a>

          @if ($hasSubmenu)
            @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
          @endif
        </li>
      @endforeach
    </ul>
  </div>
</aside>
<!-- / Horizontal Menu -->
