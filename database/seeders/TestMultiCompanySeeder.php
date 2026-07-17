<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class TestMultiCompanySeeder extends Seeder
{
  private string $password = 'Password123!';

  public function run(): void
  {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    /**
     * Pastikan role dan permission dasar sudah ada.
     * Seeder ini aman dipanggil ulang.
     */
    $this->call(RolePermissionSeeder::class);

    $fisnet = $this->createCompany([
      'code' => 'FISNET-BOGOR',
      'name' => 'FISNET Bogor',
      'email' => 'fisnetbogor01@gmail.com',
      'phone' => '081111111101',
      'address' => 'Bogor',
      'status' => 'active',
      'is_active' => true,
    ]);

    $ovall = $this->createCompany([
      'code' => 'OVALL-FIBER',
      'name' => 'OVALL Fiber Net',
      'email' => 'ovallfibernet@gmail.com',
      'phone' => '081111111102',
      'address' => 'Bogor',
      'status' => 'active',
      'is_active' => true,
    ]);

    $fisnetUser = $this->createUser([
      'name' => 'Admin FISNET Bogor',
      'email' => 'fisnetbogor01@gmail.com',
      'company_id' => $fisnet->id,
      'status' => 'active',
      'is_active' => true,
      'is_locked' => false,
    ]);

    $ovallOwner = $this->createUser([
      'name' => 'Owner OVALL Fiber Net',
      'email' => 'ovallfibernet@gmail.com',
      'company_id' => $ovall->id,
      'status' => 'active',
      'is_active' => true,
      'is_locked' => false,
    ]);

    $ovallTeknisi = $this->createUser([
      'name' => 'Teknisi OVALL Fiber Net',
      'email' => 'ovallfibernet01@gmail.com',
      'company_id' => $ovall->id,
      'status' => 'active',
      'is_active' => true,
      'is_locked' => false,
    ]);

    /**
     * Pivot multi-company.
     */
    $this->attachUserToCompany($fisnetUser, $fisnet, true);
    $this->attachUserToCompany($fisnetUser, $ovall, false);

    $this->attachUserToCompany($ovallOwner, $ovall, true);
    $this->attachUserToCompany($ovallTeknisi, $ovall, true);

    /**
     * Role per company.
     */
    $this->syncRoleForCompany($fisnetUser, $fisnet->id, ['company-admin']);
    $this->syncRoleForCompany($fisnetUser, $ovall->id, ['viewer']);

    $this->syncRoleForCompany($ovallOwner, $ovall->id, ['company-owner']);
    $this->syncRoleForCompany($ovallTeknisi, $ovall->id, ['technician']);

    /**
     * Menu/sidebar permission test.
     */
    $this->seedMenuPermissionTest();

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->command?->info('Test multi-company seeder selesai.');
    $this->command?->line('');
    $this->command?->line('Email login test:');
    $this->command?->line('- fisnetbogor01@gmail.com / Password123!');
    $this->command?->line('- ovallfibernet@gmail.com / Password123!');
    $this->command?->line('- ovallfibernet01@gmail.com / Password123!');
    $this->command?->line('');
    $this->command?->line('Scenario:');
    $this->command?->line('- fisnetbogor01@gmail.com punya 2 company: FISNET company-admin, OVALL viewer.');
    $this->command?->line('- ovallfibernet@gmail.com owner OVALL.');
    $this->command?->line('- ovallfibernet01@gmail.com technician OVALL.');
  }

  private function createCompany(array $data): Company
  {
    if (!Schema::hasTable('companies')) {
      throw new \RuntimeException('Tabel companies belum ada.');
    }

    $lookupColumn = Schema::hasColumn('companies', 'code') ? 'code' : 'name';
    $lookupValue = $data[$lookupColumn];

    $company = Company::query()
      ->where($lookupColumn, $lookupValue)
      ->first();

    if (!$company) {
      $company = new Company();
    }

    $payload = $this->onlyExistingColumns('companies', $data);

    foreach ($payload as $column => $value) {
      $company->{$column} = $value;
    }

    $company->save();

    return $company;
  }

  private function createUser(array $data): User
  {
    if (!Schema::hasTable('users')) {
      throw new \RuntimeException('Tabel users belum ada.');
    }

    $user = User::query()
      ->where('email', $data['email'])
      ->first();

    if (!$user) {
      $user = new User();
    }

    $payload = array_merge($data, [
      'password' => Hash::make($this->password),
      'email_verified_at' => now(),
      'last_activity_at' => now(),
    ]);

    $payload = $this->onlyExistingColumns('users', $payload);

    foreach ($payload as $column => $value) {
      $user->{$column} = $value;
    }

    $user->save();

    return $user;
  }

  private function attachUserToCompany(User $user, Company $company, bool $isDefault = false): void
  {
    if (!Schema::hasTable('company_user')) {
      return;
    }

    $payload = [
      'company_id' => $company->id,
      'user_id' => $user->id,
      'is_default' => $isDefault,
      'is_active' => true,
      'created_at' => now(),
      'updated_at' => now(),
    ];

    $payload = $this->onlyExistingColumns('company_user', $payload);

    DB::table('company_user')->updateOrInsert(
      [
        'company_id' => $company->id,
        'user_id' => $user->id,
      ],
      $payload
    );

    if ($isDefault && Schema::hasColumn('users', 'company_id')) {
      $user->forceFill([
        'company_id' => $company->id,
      ])->save();
    }
  }

  private function syncRoleForCompany(User $user, int $companyId, array $roles): void
  {
    if (function_exists('setPermissionsTeamId')) {
      setPermissionsTeamId($companyId);
    }

    $user->syncRoles($roles);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
  }

  private function seedMenuPermissionTest(): void
  {
    if (!Schema::hasTable('modules') || !Schema::hasTable('menus')) {
      $this->command?->warn('Tabel modules / menus belum ada. Seed menu permission dilewati.');
      return;
    }

    $mainModuleId = $this->upsertModule([
      'code' => 'MAIN',
      'name' => 'Main',
      'slug' => 'main',
      'description' => 'Main application menu',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 1,
    ]);

    $settingModuleId = $this->upsertModule([
      'code' => 'SETTING',
      'name' => 'Setting',
      'slug' => 'setting',
      'description' => 'Setting and access menu',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 2,
    ]);

    $businessModuleId = $this->upsertModule([
      'code' => 'BUSINESS',
      'name' => 'Business',
      'slug' => 'business',
      'description' => 'Business operation menu',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 3,
    ]);

    /**
     * Main menu.
     */
    $this->upsertMenu([
      'module_id' => $mainModuleId,
      'parent_id' => null,
      'name' => 'Dashboard',
      'slug' => 'dashboard',
      'url' => 'dashboard',
      'route_name' => 'dashboard',
      'route' => 'dashboard',
      'icon' => 'menu-icon tf-icons ri ri-dashboard-line icon-18px',
      'permission_name' => 'dashboard.view',
      'permission' => 'dashboard.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 1,
    ]);

    $this->upsertMenu([
      'module_id' => $mainModuleId,
      'parent_id' => null,
      'name' => 'Data Karyawan',
      'slug' => 'employees',
      'url' => 'employees',
      'route_name' => 'employees.index',
      'route' => 'employees.index',
      'icon' => 'menu-icon tf-icons ri ri-team-line icon-18px',
      'permission_name' => 'employees.view',
      'permission' => 'employees.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 10,
    ]);

    $this->upsertMenu([
      'module_id' => $mainModuleId,
      'parent_id' => null,
      'name' => 'Absensi',
      'slug' => 'attendance',
      'url' => 'attendance',
      'route_name' => 'attendance.index',
      'route' => 'attendance.index',
      'icon' => 'menu-icon tf-icons ri ri-calendar-check-line icon-18px',
      'permission_name' => 'attendance.view',
      'permission' => 'attendance.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 20,
    ]);

    $this->upsertMenu([
      'module_id' => $mainModuleId,
      'parent_id' => null,
      'name' => 'KPI',
      'slug' => 'kpi',
      'url' => 'kpi',
      'route_name' => 'kpi.index',
      'route' => 'kpi.index',
      'icon' => 'menu-icon tf-icons ri ri-bar-chart-box-line icon-18px',
      'permission_name' => 'kpi.view',
      'permission' => 'kpi.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 30,
    ]);

    $this->upsertMenu([
      'module_id' => $mainModuleId,
      'parent_id' => null,
      'name' => 'Payroll',
      'slug' => 'payroll',
      'url' => 'payroll',
      'route_name' => 'payroll.index',
      'route' => 'payroll.index',
      'icon' => 'menu-icon tf-icons ri ri-money-dollar-circle-line icon-18px',
      'permission_name' => 'payroll.view',
      'permission' => 'payroll.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 40,
    ]);

    $this->upsertMenu([
      'module_id' => $mainModuleId,
      'parent_id' => null,
      'name' => 'Approval',
      'slug' => 'approvals',
      'url' => 'approvals',
      'route_name' => 'approvals.index',
      'route' => 'approvals.index',
      'icon' => 'menu-icon tf-icons ri ri-checkbox-circle-line icon-18px',
      'permission_name' => 'approvals.view',
      'permission' => 'approvals.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 50,
    ]);

    /**
     * Settings parent.
     */
    $settingsParentId = $this->upsertMenu([
      'module_id' => $settingModuleId,
      'parent_id' => null,
      'name' => 'Settings',
      'slug' => 'settings',
      'url' => null,
      'route_name' => null,
      'route' => null,
      'icon' => 'menu-icon tf-icons ri ri-settings-3-line icon-18px',
      'permission_name' => null,
      'permission' => null,
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 80,
    ]);

    $this->upsertMenu([
      'module_id' => $settingModuleId,
      'parent_id' => $settingsParentId,
      'name' => 'User Access',
      'slug' => 'settings.user-access',
      'url' => 'settings/user-access',
      'route_name' => 'settings.user-access.index',
      'route' => 'settings.user-access.index',
      'icon' => 'menu-icon tf-icons ri ri-user-settings-line icon-18px',
      'permission_name' => 'user.view',
      'permission' => 'user.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 11,
    ]);

    $this->upsertMenu([
      'module_id' => $settingModuleId,
      'parent_id' => $settingsParentId,
      'name' => 'Role Permission',
      'slug' => 'settings.role-permission',
      'url' => 'settings/role-permission',
      'route_name' => 'settings.role-permission.index',
      'route' => 'settings.role-permission.index',
      'icon' => 'menu-icon tf-icons ri ri-shield-keyhole-line icon-18px',
      'permission_name' => 'role.view',
      'permission' => 'role.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 12,
    ]);

    $this->upsertMenu([
      'module_id' => $settingModuleId,
      'parent_id' => $settingsParentId,
      'name' => 'Company',
      'slug' => 'settings.company',
      'url' => 'settings/company',
      'route_name' => 'settings.company.index',
      'route' => 'settings.company.index',
      'icon' => 'menu-icon tf-icons ri ri-building-4-line icon-18px',
      'permission_name' => 'company.view',
      'permission' => 'company.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 13,
    ]);

    $this->upsertMenu([
      'module_id' => $settingModuleId,
      'parent_id' => $settingsParentId,
      'name' => 'User Login',
      'slug' => 'users',
      'url' => 'settings/users',
      'route_name' => 'settings.users.index',
      'route' => 'settings.users.index',
      'icon' => 'menu-icon tf-icons ri ri-user-line icon-18px',
      'permission_name' => 'users.view',
      'permission' => 'users.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 14,
    ]);

    $this->upsertMenu([
      'module_id' => $settingModuleId,
      'parent_id' => $settingsParentId,
      'name' => 'Role & Permission',
      'slug' => 'roles',
      'url' => 'settings/roles',
      'route_name' => 'settings.roles.index',
      'route' => 'settings.roles.index',
      'icon' => 'menu-icon tf-icons ri ri-shield-user-line icon-18px',
      'permission_name' => 'roles.view',
      'permission' => 'roles.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 15,
    ]);

    $this->upsertMenu([
      'module_id' => $settingModuleId,
      'parent_id' => $settingsParentId,
      'name' => 'Menu Management',
      'slug' => 'menus',
      'url' => 'settings/menus',
      'route_name' => 'settings.menus.index',
      'route' => 'settings.menus.index',
      'icon' => 'menu-icon tf-icons ri ri-menu-search-line icon-18px',
      'permission_name' => 'menus.view',
      'permission' => 'menus.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 16,
    ]);

    $this->upsertMenu([
      'module_id' => $settingModuleId,
      'parent_id' => $settingsParentId,
      'name' => 'Audit Log',
      'slug' => 'audit-logs',
      'url' => 'settings/audit-logs',
      'route_name' => 'settings.audit-logs.index',
      'route' => 'settings.audit-logs.index',
      'icon' => 'menu-icon tf-icons ri ri-file-list-3-line icon-18px',
      'permission_name' => 'audit-logs.view',
      'permission' => 'audit-logs.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 17,
    ]);

    /**
     * Business menu.
     */
    $financeParentId = $this->upsertMenu([
      'module_id' => $businessModuleId,
      'parent_id' => null,
      'name' => 'Finance',
      'slug' => 'finance',
      'url' => null,
      'route_name' => null,
      'route' => null,
      'icon' => 'menu-icon tf-icons ri ri-wallet-3-line icon-18px',
      'permission_name' => 'finance.dashboard',
      'permission' => 'finance.dashboard',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 90,
    ]);

    $this->upsertMenu([
      'module_id' => $businessModuleId,
      'parent_id' => $financeParentId,
      'name' => 'Invoice',
      'slug' => 'finance.invoice',
      'url' => 'finance/invoices',
      'route_name' => 'finance.invoices.index',
      'route' => 'finance.invoices.index',
      'icon' => 'menu-icon tf-icons ri ri-file-text-line icon-18px',
      'permission_name' => 'finance.invoice.view',
      'permission' => 'finance.invoice.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 91,
    ]);

    $nocParentId = $this->upsertMenu([
      'module_id' => $businessModuleId,
      'parent_id' => null,
      'name' => 'NOC',
      'slug' => 'noc',
      'url' => null,
      'route_name' => null,
      'route' => null,
      'icon' => 'menu-icon tf-icons ri ri-server-line icon-18px',
      'permission_name' => 'noc.dashboard',
      'permission' => 'noc.dashboard',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 100,
    ]);

    $this->upsertMenu([
      'module_id' => $businessModuleId,
      'parent_id' => $nocParentId,
      'name' => 'Monitoring',
      'slug' => 'noc.monitoring',
      'url' => 'noc/monitoring',
      'route_name' => 'noc.monitoring.index',
      'route' => 'noc.monitoring.index',
      'icon' => 'menu-icon tf-icons ri ri-pulse-line icon-18px',
      'permission_name' => 'noc.monitoring.view',
      'permission' => 'noc.monitoring.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 101,
    ]);

    $this->upsertMenu([
      'module_id' => $businessModuleId,
      'parent_id' => null,
      'name' => 'Technician Ticket',
      'slug' => 'technician.ticket',
      'url' => 'technician/tickets',
      'route_name' => 'technician.tickets.index',
      'route' => 'technician.tickets.index',
      'icon' => 'menu-icon tf-icons ri ri-tools-line icon-18px',
      'permission_name' => 'technician.ticket.view',
      'permission' => 'technician.ticket.view',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 110,
    ]);

    $this->upsertMenu([
      'module_id' => $businessModuleId,
      'parent_id' => null,
      'name' => 'Marketing',
      'slug' => 'marketing',
      'url' => 'marketing',
      'route_name' => 'marketing.dashboard',
      'route' => 'marketing.dashboard',
      'icon' => 'menu-icon tf-icons ri ri-megaphone-line icon-18px',
      'permission_name' => 'marketing.dashboard',
      'permission' => 'marketing.dashboard',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 120,
    ]);

    $this->upsertMenu([
      'module_id' => $businessModuleId,
      'parent_id' => null,
      'name' => 'Sales',
      'slug' => 'sales',
      'url' => 'sales',
      'route_name' => 'sales.dashboard',
      'route' => 'sales.dashboard',
      'icon' => 'menu-icon tf-icons ri ri-line-chart-line icon-18px',
      'permission_name' => 'sales.dashboard',
      'permission' => 'sales.dashboard',
      'is_active' => true,
      'is_visible' => true,
      'sort_order' => 130,
    ]);
  }

  private function upsertModule(array $data): int
  {
    $lookup = $this->resolveLookupColumn('modules', ['code', 'slug', 'name']);
    $lookupValue = $data[$lookup] ?? $data['name'];

    $existing = DB::table('modules')
      ->where($lookup, $lookupValue)
      ->first();

    $payload = $this->onlyExistingColumns('modules', array_merge($data, [
      'created_at' => now(),
      'updated_at' => now(),
    ]));

    if ($existing) {
      unset($payload['created_at']);

      DB::table('modules')
        ->where('id', $existing->id)
        ->update($payload);

      return (int) $existing->id;
    }

    return (int) DB::table('modules')->insertGetId($payload);
  }

  private function upsertMenu(array $data): int
  {
    /**
     * Kalau tabel menus punya kolom code, wajib isi code.
     * Ambil dari slug, kalau slug kosong ambil dari name.
     */
    if (Schema::hasColumn('menus', 'code') && empty($data['code'])) {
      $data['code'] = $data['slug'] ?? Str::slug($data['name'] ?? 'menu', '-');
    }

    /**
     * Kalau code tersedia, lookup cukup by code saja.
     * Karena menus.code unique global.
     */
    if (Schema::hasColumn('menus', 'code') && !empty($data['code'])) {
      $existing = DB::table('menus')
        ->where('code', $data['code'])
        ->first();
    } else {
      $lookup = $this->resolveLookupColumn('menus', ['slug', 'name']);
      $lookupValue = $data[$lookup] ?? $data['name'];

      $query = DB::table('menus')->where($lookup, $lookupValue);

      if (Schema::hasColumn('menus', 'module_id') && isset($data['module_id'])) {
        $query->where('module_id', $data['module_id']);
      }

      if (Schema::hasColumn('menus', 'parent_id')) {
        if (array_key_exists('parent_id', $data) && $data['parent_id']) {
          $query->where('parent_id', $data['parent_id']);
        } else {
          $query->whereNull('parent_id');
        }
      }

      $existing = $query->first();
    }

    $payload = $this->onlyExistingColumns('menus', array_merge($data, [
      'created_at' => now(),
      'updated_at' => now(),
    ]));

    if ($existing) {
      unset($payload['created_at']);

      DB::table('menus')
        ->where('id', $existing->id)
        ->update($payload);

      return (int) $existing->id;
    }

    return (int) DB::table('menus')->insertGetId($payload);
  }

  private function resolveLookupColumn(string $table, array $columns): string
  {
    foreach ($columns as $column) {
      if (Schema::hasColumn($table, $column)) {
        return $column;
      }
    }

    return 'id';
  }

  private function onlyExistingColumns(string $table, array $data): array
  {
    return collect($data)
      ->filter(fn($value, $column) => Schema::hasColumn($table, $column))
      ->all();
  }
}
