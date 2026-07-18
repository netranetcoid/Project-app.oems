<?php

use App\Http\Middleware\EnsureCompanySelected;
use App\Http\Middleware\SetPermissionCompanyContext;
use App\Http\Middleware\AuditMutation;
use App\Http\Middleware\EnsureAppBillIntegrationRequest;
use App\Http\Middleware\EnsureMobilePasswordChanged;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    // API mobile OvallHR memakai prefix versi agar kontrak endpoint dapat
    // berevolusi tanpa memutus versi aplikasi yang sudah terpasang.
    api: __DIR__ . '/../routes/api.php',
    apiPrefix: 'api/v1',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
      'company.selected' => EnsureCompanySelected::class,
      'permission.company.context' => SetPermissionCompanyContext::class,
      'audit.mutation' => AuditMutation::class,
      'appbill.integration' => EnsureAppBillIntegrationRequest::class,
      // Alias singular dipertahankan sementara untuk route APK lama yang
      // pernah memakai `mobile.password.change`; canonical di API baru adalah
      // `mobile.password.changed`.
      'mobile.password.change' => EnsureMobilePasswordChanged::class,
      'mobile.password.changed' => EnsureMobilePasswordChanged::class,

      'role' => RoleMiddleware::class,
      'permission' => PermissionMiddleware::class,
      'role_or_permission' => RoleOrPermissionMiddleware::class,
    ]);

    // Semua mutasi web dan API diberi request ID serta audit metadata. Nilai
    // sensitif tidak disalin oleh middleware audit.
    $middleware->appendToGroup('web', AuditMutation::class);
    $middleware->appendToGroup('api', AuditMutation::class);

    $middleware->priority([
      \Illuminate\Session\Middleware\StartSession::class,
      \Illuminate\View\Middleware\ShareErrorsFromSession::class,
      \Illuminate\Auth\Middleware\Authenticate::class,
      EnsureCompanySelected::class,
      SetPermissionCompanyContext::class,
      RoleMiddleware::class,
      PermissionMiddleware::class,
      RoleOrPermissionMiddleware::class,
      \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ]);
  })
  ->withSchedule(function (Schedule $schedule): void {
    // Bukti selfie/GPS dipurge harian; rekap absensi tidak dihapus.
    $schedule->command('attendance:purge-proofs')->dailyAt('02:15');
    // Pembersihan log dilakukan di luar jam kerja; payroll dan kegagalan
    // integrasi tidak termasuk pembersihan otomatis.
    $schedule->command('observability:prune')->dailyAt('03:10')->withoutOverlapping();
    // Outbox idempotent aman dijalankan berulang. Selama mode mock tidak ada
    // request yang keluar ke AppBill atau internet.
    $schedule->command('integration:dispatch-outbox --limit=50')->everyMinute()->withoutOverlapping();
  })
  ->withExceptions(function (Exceptions $exceptions): void {
    //
  })
  ->create();
