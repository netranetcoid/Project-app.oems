<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class MenuService
{
  public function getMenuData(): array
  {
    $menus = $this->getMenus();

    return [
      (object) [
        'menu' => $menus,
      ],
      (object) [
        'menu' => $menus,
      ],
    ];
  }

  public function getMenus(): Collection
  {
    if (!Schema::hasTable('menus')) {
      return collect();
    }

    $query = DB::table('menus');

    if (Schema::hasColumn('menus', 'deleted_at')) {
      $query->whereNull('deleted_at');
    }

    if (Schema::hasColumn('menus', 'is_active')) {
      $query->where('is_active', true);
    }

    if (Schema::hasColumn('menus', 'is_visible')) {
      $query->where('is_visible', true);
    }

    if (Schema::hasColumn('menus', 'sort_order')) {
      $query->orderBy('sort_order');
    }

    $query->orderBy('id');

    $menus = $query->get()->map(function ($menu) {
      return $this->normalizeMenu($menu);
    });

    return $this->buildTree($menus);
  }

  private function buildTree(Collection $menus, ?int $parentId = null): Collection
  {
    return $menus
      ->filter(function ($menu) use ($parentId) {
        $menuParentId = $menu->parent_id ?? null;

        if ($parentId === null) {
          return empty($menuParentId);
        }

        return (int) $menuParentId === (int) $parentId;
      })
      ->map(function ($menu) use ($menus) {
        $children = $this->buildTree($menus, (int) $menu->id);

        if ($children->isNotEmpty()) {
          $menu->submenu = $children;
        }

        return $menu;
      })
      ->values();
  }

  private function normalizeMenu(object $menu): object
  {
    $normalized = new stdClass();

    foreach ((array) $menu as $key => $value) {
      $normalized->{$key} = $value;
    }

    $normalized->name = $menu->name ?? $menu->label ?? '-';

    // Route index dipakai sebagai prefix active-state. Contoh
    // hr.payroll.index tetap aktif ketika membuka hr.payroll.show/payslip.
    $routeName = $menu->route_name ?? null;
    $normalized->slug = $routeName && str_ends_with($routeName, '.index')
      ? substr($routeName, 0, -6)
      : ($routeName ?? ($menu->code ?? null));

    $normalized->url = !empty($menu->url)
      ? ltrim((string) $menu->url, '/')
      : null;

    $normalized->icon = $menu->icon ?? null;

    $normalized->permission = $menu->permission_name ?? null;
    $normalized->permission_name = $menu->permission_name ?? null;

    $normalized->target = !empty($menu->open_in_new_tab)
      ? '_blank'
      : ($menu->target ?? '');

    return $normalized;
  }
}
