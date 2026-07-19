@php
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route;

  // Data awal dibuat oleh AppServiceProvider. JavaScript di bawah melakukan
  // polling ringan sehingga HR yang sedang membuka AppOEMS tetap tahu ada
  // pengajuan baru tanpa menekan refresh browser.
  $ovallHrNotifications = $ovallHrUnreadNotifications ?? collect();
  $canReviewOvallHrRequests = Auth::check() && Auth::user()->can('hr-request.view');
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
  <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-6">
    <a href="{{ url('/') }}" class="app-brand-link gap-2">
      <span class="app-brand-logo demo">@include('_partials.macros')</span>
      <span class="app-brand-text demo menu-text fw-semibold ms-1">{{ config('variables.templateName') }}</span>
    </a>

    <!-- Display menu close icon only for horizontal-menu with navbar-full -->
    @if (isset($menuHorizontal))
      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
        <i class="icon-base ri ri-close-line icon-sm"></i>
      </a>
    @endif
  </div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
  <div
    class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="icon-base ri ri-menu-line icon-md"></i>
    </a>
  </div>
@endif

<div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
  @if ($configData['hasCustomizer'] == true)
    <!-- Search -->
    <div class="navbar-nav align-items-center">
      <li class="nav-item dropdown me-2 me-xl-0">
        <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);"
          data-bs-toggle="dropdown">
          <i class="icon-base ri ri-sun-line icon-md theme-icon-active"></i>
          <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="nav-theme-text">
          <li>
            <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light"
              aria-pressed="false">
              <span><i class="icon-base ri ri-sun-line icon-22px me-3" data-icon="sun-line"></i>Light</span>
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark"
              aria-pressed="true">
              <span><i class="icon-base ri ri-moon-clear-line icon-22px me-3"
                  data-icon="moon-clear-line"></i>Dark</span>
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"
              aria-pressed="false">
              <span><i class="icon-base ri ri-computer-line icon-22px me-3" data-icon="computer-line"></i>System</span>
            </button>
          </li>
        </ul>
      </li>
    </div>
    <!-- / Style Switcher-->
  @endif
  <ul class="navbar-nav flex-row align-items-center ms-auto">
    @if ($canReviewOvallHrRequests)
      <!-- OvallHR request inbox: only HR/reviewer can see this floating bell. -->
      <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2" id="ovallhr-notification-root">
        <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);" data-bs-toggle="dropdown"
          aria-label="Notifikasi pengajuan OvallHR" aria-expanded="false">
          <i class="icon-base ri ri-notification-3-line icon-22px"></i>
          <span id="ovallhr-notification-badge"
            class="badge rounded-pill bg-danger badge-dot badge-notifications {{ $ovallHrNotifications->isEmpty() ? 'd-none' : '' }}"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0" style="min-width: 22rem; max-width: min(22rem, calc(100vw - 2rem));">
          <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h6 class="mb-0 me-auto">Pengajuan OvallHR</h6>
              <span class="badge rounded-pill bg-label-primary" id="ovallhr-notification-count">{{ $ovallHrNotifications->count() }}</span>
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container" style="max-height: 22rem; overflow-y: auto;">
            <ul class="list-group list-group-flush" id="ovallhr-notification-list">
              @forelse ($ovallHrNotifications as $notification)
                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                  <a class="d-flex gap-3 text-decoration-none text-reset" href="{{ route('notifications.ovallhr.open', $notification->id) }}">
                    <span class="avatar flex-shrink-0"><span class="avatar-initial rounded bg-label-warning"><i class="icon-base ri ri-file-edit-line"></i></span></span>
                    <span class="d-flex flex-column gap-1">
                      <span class="mb-0 fw-medium">{{ data_get($notification->data, 'title', 'Pengajuan baru') }}</span>
                      <span class="text-body small">{{ data_get($notification->data, 'message', '') }}</span>
                      <span class="text-body-secondary small">{{ $notification->created_at?->diffForHumans() }}</span>
                    </span>
                  </a>
                </li>
              @empty
                <li class="list-group-item text-center text-body-secondary py-4" id="ovallhr-notification-empty">Belum ada pengajuan yang menunggu review.</li>
              @endforelse
            </ul>
          </li>
          <li class="dropdown-menu-footer border-top">
            <a href="{{ route('hr.requests.index') }}" class="dropdown-item text-center py-2">Buka Pengajuan &amp; Approval</a>
          </li>
        </ul>
      </li>
    @endif
    <!-- User -->
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
      <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
          <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}"
            alt="avatar" class="rounded-circle" />
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
        <li>
          <a class="dropdown-item"
            href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-2">
                <div class="avatar avatar-online">
                  <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}"
                    alt="alt" class="w-px-40 h-auto rounded-circle" />
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0 small">
                  @if (Auth::check())
                    {{ Auth::user()->name }}
                  @else
                    John Doe
                  @endif
                </h6>
                <small class="text-body-secondary">Admin</small>
              </div>
            </div>
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li>
          <a class="dropdown-item"
            href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
            <i class="icon-base ri ri-user-3-line icon-22px me-2"></i> <span class="align-middle">My
              Profile</span> </a>
        </li>
        @if (Auth::check() &&
                class_exists(\Laravel\Jetstream\Jetstream::class) &&
                \Laravel\Jetstream\Jetstream::hasApiFeatures())
          <li>
            <a class="dropdown-item" href="{{ route('api-tokens.index') }}"> <i
                class="icon-base ri ri-settings-4-line icon-22px me-3"></i><span class="align-middle">Settings</span>
            </a>
          </li>
        @endif
        <li>
          <a class="dropdown-item" href="javascript:void(0);">
            <span class="d-flex align-items-center align-middle">
              <i class="flex-shrink-0 icon-base ri ri-file-text-line icon-22px me-3"></i>
              <span class="flex-grow-1 align-middle">Billing Plan</span>
              <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger">4</span>
            </span>
          </a>
        </li>
        @if (Auth::user() &&
                class_exists(\Laravel\Jetstream\Jetstream::class) &&
                \Laravel\Jetstream\Jetstream::hasTeamFeatures())
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li>
            <h6 class="dropdown-header">Manage Team</h6>
          </li>
          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
            <a class="dropdown-item"
              href="{{ Auth::user() ? route('teams.show', Auth::user()->currentTeam->id) : 'javascript:void(0)' }}">
              <i class="icon-base ri ri-settings-3-line icon-md me-3"></i><span>Team Settings</span>
            </a>
          </li>
          @if (class_exists(\Laravel\Jetstream\Jetstream::class))
            @can('create', \Laravel\Jetstream\Jetstream::newTeamModel())
              <li>
                <a class="dropdown-item" href="{{ route('teams.create') }}">
                  <i class="icon-base ri ri-group-line icon-md me-3"></i><span>Create New Team</span>
                </a>
              </li>
            @endcan
          @endif
          @if (Auth::user()->allTeams()->count() > 1)
            <li>
              <div class="dropdown-divider my-1"></div>
            </li>
            <li>
              <h6 class="dropdown-header">Switch Teams</h6>
            </li>
            <li>
              <div class="dropdown-divider my-1"></div>
            </li>
          @endif
          @if (Auth::user())
            @foreach (Auth::user()->allTeams() as $team)
              {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}

              {{-- <x-switchable-team :team="$team" /> --}}
            @endforeach
          @endif
        @endif
        <li>
          <div class="dropdown-divider my-1"></div>
        </li>
        @if (Auth::check())
          <li>
            <div class="d-grid px-4 pt-2 pb-1">
              <a class="btn btn-danger d-flex" href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <small class=" align-middle">Logout</small>
                <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
              </a>
            </div>
          </li>
          <form method="POST" id="logout-form" action="{{ route('logout') }}">
            @csrf
          </form>
        @else
          <li>
            <div class="d-grid px-4 pt-2 pb-1">
              <a class="btn btn-danger d-flex"
                href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                <small class="align-middle">Login</small>
                <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
              </a>
            </div>
          </li>
        @endif
      </ul>
    </li>
    <!--/ User -->
  </ul>
</div>

@if ($canReviewOvallHrRequests)
  <!-- Toast sengaja ringan (tanpa websocket) agar cocok untuk VPS saat ini. -->
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div id="ovallhr-request-toast" class="toast text-bg-primary border-0" role="status" aria-live="polite" aria-atomic="true">
      <div class="d-flex"><div class="toast-body"><i class="icon-base ri ri-notification-3-line me-1"></i> Ada pengajuan OvallHR baru untuk direview.</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button></div>
    </div>
  </div>
  <script>
    (() => {
      const endpoint = @json(route('notifications.ovallhr.index'));
      const openPrefix = @json(url('/notifications/ovallhr'));
      const list = document.getElementById('ovallhr-notification-list');
      const badge = document.getElementById('ovallhr-notification-badge');
      const count = document.getElementById('ovallhr-notification-count');
      let knownUnread = Number(count?.textContent || 0);

      const escapeHtml = (value) => String(value || '').replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[char]);
      const render = (payload) => {
        const unread = Number(payload.unread_count || 0);
        count.textContent = unread;
        badge.classList.toggle('d-none', unread === 0);
        const items = payload.notifications || [];
        list.innerHTML = items.length
          ? items.map((item) => `<li class="list-group-item list-group-item-action dropdown-notifications-item"><a class="d-flex gap-3 text-decoration-none text-reset" href="${escapeHtml(item.open_url || openPrefix + '/' + item.id + '/open')}"><span class="avatar flex-shrink-0"><span class="avatar-initial rounded bg-label-warning"><i class="icon-base ri ri-file-edit-line"></i></span></span><span class="d-flex flex-column gap-1"><span class="mb-0 fw-medium">${escapeHtml(item.title)}</span><span class="text-body small">${escapeHtml(item.message)}</span><span class="text-body-secondary small">${escapeHtml(item.created_at)}</span></span></a></li>`).join('')
          : '<li class="list-group-item text-center text-body-secondary py-4">Belum ada pengajuan yang menunggu review.</li>';
        if (unread > knownUnread && window.bootstrap?.Toast) {
          window.bootstrap.Toast.getOrCreateInstance(document.getElementById('ovallhr-request-toast'), { delay: 8000 }).show();
        }
        knownUnread = unread;
      };
      const refresh = () => fetch(endpoint, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then((response) => response.ok ? response.json() : null)
        .then((response) => response?.data && render(response.data))
        .catch(() => {});
      window.setTimeout(refresh, 5000);
      window.setInterval(refresh, 30000);
    })();
  </script>
@endif
