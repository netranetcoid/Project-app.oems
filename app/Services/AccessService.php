<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class AccessService
{
  public function isSuperAdmin(User $user): bool
  {
    try {
      if (Schema::hasColumn('users', 'is_super_admin') && $user->is_super_admin) {
        return true;
      }

      return $user->hasRole('super-admin');
    } catch (Throwable) {
      return false;
    }
  }

  public function isCompanyAdmin(User $user): bool
  {
    try {
      return $user->hasAnyRole([
        'company-owner',
        'company-admin',
        'super-admin',
      ]);
    } catch (Throwable) {
      return false;
    }
  }

  public function currentCompanyId(): ?int
  {
    return session('company_id') ? (int) session('company_id') : null;
  }

  public function ensureUserBelongsToCompany(User $user, int $companyId): bool
  {
    if ($this->isSuperAdmin($user)) {
      return true;
    }

    try {
      if (method_exists($user, 'activeCompanies')) {
        return $user->activeCompanies()
          ->where('companies.id', $companyId)
          ->exists();
      }
    } catch (Throwable $e) {
      report($e);
    }

    if (Schema::hasColumn('users', 'company_id')) {
      return (int) $user->company_id === $companyId;
    }

    return false;
  }

  public function syncDefaultRoleByDivisionPosition(User $user, int $companyId): void
  {
    if (function_exists('setPermissionsTeamId')) {
      setPermissionsTeamId($companyId);
    }

    if ($user->roles()->count() > 0) {
      return;
    }

    $divisionName = StrtolowerSafe($user->division?->code ?? $user->division?->name ?? '');
    $positionName = StrtolowerSafe($user->position?->code ?? $user->position?->name ?? '');

    $role = match (true) {
      str_contains($positionName, 'owner') => 'company-owner',
      str_contains($positionName, 'admin') => 'company-admin',
      str_contains($positionName, 'viewer') => 'viewer',
      str_contains($divisionName, 'management') => 'management',
      str_contains($divisionName, 'finance') => 'finance',
      str_contains($divisionName, 'hr') => 'hr',
      str_contains($divisionName, 'noc') => 'noc',
      str_contains($divisionName, 'teknisi'),
      str_contains($divisionName, 'technician') => 'technician',
      str_contains($divisionName, 'marketing') => 'marketing',
      str_contains($divisionName, 'sales') => 'sales',
      default => 'viewer',
    };

    $user->assignRole($role);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
  }

  public function getAvailableRolesForCompany(int $companyId): Collection
  {
    if (function_exists('setPermissionsTeamId')) {
      setPermissionsTeamId($companyId);
    }

    return Role::query()
      ->where('guard_name', 'web')
      ->when(Schema::hasColumn('roles', 'company_id'), function ($query) use ($companyId) {
        $query->where(function ($q) use ($companyId) {
          $q->whereNull('company_id')
            ->orWhere('company_id', $companyId);
        });
      })
      ->orderBy('name')
      ->get();
  }

  public function getAvailablePermissions(): Collection
  {
    return Permission::query()
      ->where('guard_name', 'web')
      ->orderBy('name')
      ->get();
  }
}

if (!function_exists('App\Services\StrtolowerSafe')) {
  function StrtolowerSafe(?string $value): string
  {
    return mb_strtolower(trim((string) $value));
  }
}
