<?php

namespace App\Providers;

use App\Services\MenuService;
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
        if (!Schema::hasTable('menus')) {
          return;
        }

        $menuData = app(MenuService::class)->getMenuData();

        if (
          isset($menuData[0]->menu)
          && collect($menuData[0]->menu)->isNotEmpty()
        ) {
          $view->with('menuData', $menuData);
        }
      } catch (Throwable $e) {
        report($e);
      }
    });
  }
}
