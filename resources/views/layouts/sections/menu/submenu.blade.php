<ul class="menu-sub">
  @foreach ($menu as $submenu)
    @php
      $hasSubmenu = isset($submenu->submenu) && count($submenu->submenu) > 0;
      $activeClass = $isActiveMenu($submenu) ? ($hasSubmenu ? 'active open' : 'active') : '';
      $target = !empty($submenu->target) ? $submenu->target : null;
    @endphp

    <li class="menu-item {{ $activeClass }}">
      <a href="{{ $hasSubmenu ? 'javascript:void(0);' : $menuUrl($submenu) }}"
        class="menu-link {{ $hasSubmenu ? 'menu-toggle' : '' }}"
        @if ($target) target="{{ $target }}" @endif>

        @if (!empty($submenu->icon))
          <i class="{{ $submenu->icon }}"></i>
        @endif

        <div>{{ __($submenu->name ?? ($submenu->label ?? '-')) }}</div>

        @if (!empty($submenu->badge_text))
          <div class="badge bg-label-{{ $submenu->badge_color ?? 'primary' }} rounded-pill ms-auto">
            {{ $submenu->badge_text }}
          </div>
        @endif
      </a>

      @if ($hasSubmenu)
        @include('layouts.sections.menu.submenu', [
            'menu' => $submenu->submenu,
            'isActiveMenu' => $isActiveMenu,
            'menuUrl' => $menuUrl,
        ])
      @endif
    </li>
  @endforeach
</ul>
