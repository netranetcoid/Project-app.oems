<?php

namespace App\Providers;

use App\Services\MenuService;
use App\Models\Attendance;
use App\Models\Employee;
use App\Observers\AttendanceObserver;
use App\Observers\EmployeeObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    //
  }

  public function boot(): void
  {
    // Observer hanya memasukkan event ke outbox terenkripsi. HTTP AppBill
    // tetap diproses scheduler agar input absensi mobile tidak melambat.
    Attendance::observe(AttendanceObserver::class);
    Employee::observe(EmployeeObserver::class);

    RateLimiter::for('login', function (Request $request): Limit {
      $identity = Str::lower((string) (
        $request->input('login')
        ?? $request->input('username')
        ?? $request->input('identity')
        ?? $request->input('email')
        ?? 'guest'
      ));

      return Limit::perMinute(5)->by($identity . '|' . $request->ip());
    });

    Gate::before(function ($user, $ability) {
      try {
        // Bypass khusus buat email lu
        if (app()->environment('local') && $user->email === 'netra.net.co.id@gmail.com') {
          return true;
        }

        // Ini kode bawaan sistem lu sebelumnya
        return $user->hasRole('super-admin') ? true : null;
      } catch (Throwable $e) {
        return null;
      }
    });

    View::composer('*', function ($view) {
      try {
        if (Schema::hasTable('menus')) {
          $menuData = app(MenuService::class)->getMenuData();

          if (
            isset($menuData[0]->menu)
            && collect($menuData[0]->menu)->isNotEmpty()
          ) {
            $view->with('menuData', $menuData);
          }
        }

        // Navbar memakai data ini untuk lonceng pengajuan OvallHR. Query
        // ditutup oleh pengecekan tabel supaya deployment sebelum migrate
        // tidak menghasilkan HTTP 500.
        if (auth()->check() && Schema::hasTable('notifications')) {
          $notifications = auth()->user()->unreadNotifications()
            ->where('type', \App\Notifications\OvallHrEmployeeRequestSubmitted::class)
            ->latest()
            ->limit(8)
            ->get();
          $view->with('ovallHrUnreadNotifications', $notifications);
        }
      } catch (Throwable $e) {
        report($e);
      }
    });
  }
}
