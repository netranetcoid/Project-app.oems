<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Services\AccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
  private array $protectedRoles = [
    'super-admin',
    'company-owner',
    'company-admin',
  ];

  public function __construct(
    protected AccessService $accessService
  ) {}

  public function index()
  {
    $permissions = Permission::query()
      ->where('guard_name', 'web')
      ->orderBy('name')
      ->get();

    $permissionGroups = $permissions->groupBy(function ($permission) {
      return Str::before($permission->name, '.');
    });

    return view('setting.role_permission_index', [
      'permissionGroups' => $permissionGroups,
    ]);
  }

  public function data(Request $request): JsonResponse
  {
    $companyId = $this->currentCompanyId();

    $this->setCompanyContext($companyId);

    $roles = Role::query()
      ->with('permissions')
      ->where('guard_name', 'web')
      ->when(Schema::hasColumn('roles', 'company_id'), function ($query) use ($companyId) {
        $query->where(function ($q) use ($companyId) {
          $q->where('company_id', $companyId)
            ->orWhereNull('company_id');
        });
      })
      ->orderByRaw("FIELD(name, 'super-admin', 'company-owner', 'company-admin', 'management', 'finance', 'hr', 'noc', 'technician', 'marketing', 'sales', 'viewer') DESC")
      ->orderBy('name')
      ->get()
      ->map(function (Role $role) use ($request, $companyId) {
        $isProtected = in_array($role->name, $this->protectedRoles, true);
        $isGlobal = Schema::hasColumn('roles', 'company_id') && is_null($role->company_id);

        return [
          'id' => $role->id,
          'name' => $role->name,
          'scope' => $isGlobal ? 'global' : 'company',
          'company_id' => $role->company_id ?? $companyId,
          'permissions_count' => $role->permissions->count(),
          'permissions' => $role->permissions->pluck('name')->values(),
          'is_protected' => $isProtected,
          'can_edit' => $this->canManageRole($request, $role, 'edit'),
          'can_delete' => $this->canManageRole($request, $role, 'delete'),
        ];
      })
      ->values();

    return response()->json([
      'data' => $roles,
    ]);
  }

  public function store(Request $request): JsonResponse
  {
    $companyId = $this->currentCompanyId();

    $this->setCompanyContext($companyId);

    $validated = $request->validate([
      'name' => [
        'required',
        'string',
        'max:80',
        'regex:/^[a-z0-9\-_.]+$/',
      ],
      'permissions' => ['nullable', 'array'],
      'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
    ], [
      'name.regex' => 'Nama role hanya boleh huruf kecil, angka, titik, underscore, dan strip. Contoh: area-manager',
    ]);

    $roleName = Str::lower(trim($validated['name']));

    if ($roleName === 'super-admin' && !$this->accessService->isSuperAdmin($request->user())) {
      abort(403, 'Tidak boleh membuat role super-admin.');
    }

    $exists = Role::query()
      ->where('name', $roleName)
      ->where('guard_name', 'web')
      ->when(Schema::hasColumn('roles', 'company_id'), function ($query) use ($companyId) {
        $query->where('company_id', $companyId);
      })
      ->exists();

    if ($exists) {
      return response()->json([
        'message' => 'Role sudah ada di company aktif.',
      ], 422);
    }

    $attributes = [
      'name' => $roleName,
      'guard_name' => 'web',
    ];

    if (Schema::hasColumn('roles', 'company_id')) {
      $attributes['company_id'] = $companyId;
    }

    $role = Role::create($attributes);

    $permissions = $validated['permissions'] ?? [];

    if ($permissions) {
      $role->syncPermissions($permissions);
    }

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return response()->json([
      'message' => 'Role berhasil dibuat.',
      'role_id' => $role->id,
    ]);
  }

  public function edit(Request $request, Role $role): JsonResponse
  {
    $this->authorizeRoleScope($request, $role);

    $companyId = $this->currentCompanyId();

    $this->setCompanyContext($companyId);

    return response()->json([
      'role' => [
        'id' => $role->id,
        'name' => $role->name,
        'scope' => Schema::hasColumn('roles', 'company_id') && is_null($role->company_id) ? 'global' : 'company',
        'is_protected' => in_array($role->name, $this->protectedRoles, true),
      ],
      'permissions' => $role->permissions()
        ->where('guard_name', 'web')
        ->pluck('name')
        ->values(),
    ]);
  }

  public function update(Request $request, Role $role): JsonResponse
  {
    $this->authorizeRoleScope($request, $role);

    if (!$this->canManageRole($request, $role, 'edit')) {
      abort(403, 'Role ini tidak boleh diubah.');
    }

    $companyId = $this->currentCompanyId();

    $this->setCompanyContext($companyId);

    $validated = $request->validate([
      'name' => [
        'required',
        'string',
        'max:80',
        'regex:/^[a-z0-9\-_.]+$/',
      ],
      'permissions' => ['nullable', 'array'],
      'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
    ], [
      'name.regex' => 'Nama role hanya boleh huruf kecil, angka, titik, underscore, dan strip. Contoh: area-manager',
    ]);

    $newName = Str::lower(trim($validated['name']));
    $oldName = $role->name;

    if (in_array($oldName, $this->protectedRoles, true) && $newName !== $oldName) {
      abort(403, 'Role bawaan tidak boleh diganti namanya.');
    }

    if ($newName === 'super-admin' && !$this->accessService->isSuperAdmin($request->user())) {
      abort(403, 'Tidak boleh mengubah role menjadi super-admin.');
    }

    /*
  |--------------------------------------------------------------------------
  | Check duplicate hanya kalau nama berubah
  |--------------------------------------------------------------------------
  |
  | Ini penting karena di multi company / Spatie teams bisa ada role global
  | company_id null dan role company dengan nama yang sama.
  |
  */

    if ($newName !== $oldName) {
      $exists = Role::query()
        ->where('name', $newName)
        ->where('guard_name', 'web')
        ->whereKeyNot($role->id)
        ->when(Schema::hasColumn('roles', 'company_id'), function ($query) use ($role, $companyId) {
          if (is_null($role->company_id)) {
            $query->whereNull('company_id');
          } else {
            $query->where('company_id', $companyId);
          }
        })
        ->exists();

      if ($exists) {
        return response()->json([
          'message' => 'Nama role sudah dipakai di scope role ini.',
        ], 422);
      }

      $role->update([
        'name' => $newName,
      ]);
    }

    $role->syncPermissions($validated['permissions'] ?? []);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return response()->json([
      'message' => 'Role dan permission berhasil diperbarui.',
    ]);
  }
  public function destroy(Request $request, Role $role): JsonResponse
  {
    $this->authorizeRoleScope($request, $role);

    if (!$this->canManageRole($request, $role, 'delete')) {
      abort(403, 'Role ini tidak boleh dihapus.');
    }

    $assignedUsers = method_exists($role, 'users')
      ? $role->users()->count()
      : 0;

    if ($assignedUsers > 0) {
      return response()->json([
        'message' => 'Role tidak bisa dihapus karena masih digunakan oleh user.',
      ], 422);
    }

    $role->delete();

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return response()->json([
      'message' => 'Role berhasil dihapus.',
    ]);
  }

  private function currentCompanyId(): int
  {
    $companyId = $this->accessService->currentCompanyId();

    if (!$companyId) {
      abort(403, 'Company aktif belum dipilih.');
    }

    return (int) $companyId;
  }

  private function setCompanyContext(int $companyId): void
  {
    if (function_exists('setPermissionsTeamId')) {
      setPermissionsTeamId($companyId);
    }
  }

  private function authorizeRoleScope(Request $request, Role $role): void
  {
    $companyId = $this->currentCompanyId();

    if (!Schema::hasColumn('roles', 'company_id')) {
      return;
    }

    if (is_null($role->company_id)) {
      if (!$this->accessService->isSuperAdmin($request->user())) {
        abort(403, 'Role global hanya boleh dikelola super-admin.');
      }

      return;
    }

    if ((int) $role->company_id !== $companyId) {
      abort(403, 'Role bukan milik company aktif.');
    }
  }

  private function canManageRole(Request $request, Role $role, string $action): bool
  {
    $isSuperAdmin = $this->accessService->isSuperAdmin($request->user());
    $isProtected = in_array($role->name, $this->protectedRoles, true);
    $isGlobal = Schema::hasColumn('roles', 'company_id') && is_null($role->company_id);

    if ($isGlobal && !$isSuperAdmin) {
      return false;
    }

    if ($role->name === 'super-admin' && !$isSuperAdmin) {
      return false;
    }

    if ($action === 'delete' && $isProtected) {
      return false;
    }

    return true;
  }
}
