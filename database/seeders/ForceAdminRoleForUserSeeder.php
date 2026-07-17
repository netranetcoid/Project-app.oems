<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ForceAdminRoleForUserSeeder extends Seeder
{
  private string $guard = 'web';

  private array $emails = [
    'fisnetbogor01@gmail.com',
    'ovallfibernet@gmail.com',
    'ovallfibernet01@gmail.com',
  ];

  private array $permissions = [
    'dashboard.view',

    'role.view',
    'role.create',
    'role.update',
    'role.delete',
    'role.assign-permission',

    'user.view',
    'user.create',
    'user.update',
    'user.delete',
    'user.assign-role',
    'user.assign-permission',

    'company.view',
    'company.create',
    'company.update',
    'company.delete',

    'menus.view',
    'menus.create',
    'menus.update',
    'menus.delete',

    'roles.view',
    'roles.create',
    'roles.update',
    'roles.delete',
  ];

  public function run(): void
  {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach ($this->permissions as $permission) {
      Permission::firstOrCreate([
        'name' => $permission,
        'guard_name' => $this->guard,
      ]);
    }

    $companyIds = $this->companyIds();

    foreach ($companyIds as $companyId) {
      if ($companyId && function_exists('setPermissionsTeamId')) {
        setPermissionsTeamId((int) $companyId);
      }

      $rolePayload = [
        'name' => 'company-admin',
        'guard_name' => $this->guard,
      ];

      if ($companyId && Schema::hasColumn('roles', 'company_id')) {
        $rolePayload['company_id'] = (int) $companyId;
      }

      $role = Role::firstOrCreate($rolePayload);
      $role->syncPermissions($this->permissions);

      $users = User::query()
        ->whereIn('email', $this->emails)
        ->get();

      foreach ($users as $user) {
        $user->assignRole($role);
        $user->unsetRelation('roles');
      }

      $this->command?->info('company-admin fixed for company_id: ' . ($companyId ?: 'global'));
    }

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach ($this->emails as $email) {
      $user = User::where('email', $email)->first();

      if (!$user) {
        $this->command?->warn("User tidak ditemukan: {$email}");
        continue;
      }

      foreach ($companyIds as $companyId) {
        if ($companyId && function_exists('setPermissionsTeamId')) {
          setPermissionsTeamId((int) $companyId);
        }

        $user->unsetRelation('roles');

        $this->command?->info(
          $email .
            ' | company_id=' . ($companyId ?: 'global') .
            ' | roles=' . $user->getRoleNames()->implode(', ') .
            ' | role.view=' . ($user->can('role.view') ? 'true' : 'false')
        );
      }
    }
  }

  private function companyIds(): array
  {
    if (!Schema::hasTable('companies')) {
      return [null];
    }

    $query = DB::table('companies');

    if (Schema::hasColumn('companies', 'deleted_at')) {
      $query->whereNull('deleted_at');
    }

    if (Schema::hasColumn('companies', 'is_active')) {
      $query->where('is_active', 1);
    }

    $ids = $query->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter()
      ->values()
      ->all();

    return !empty($ids) ? $ids : [null];
  }
}
