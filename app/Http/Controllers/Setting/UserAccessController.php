<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Position;
use App\Models\User;
use App\Services\AccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class UserAccessController extends Controller
{
  public function __construct(
    protected AccessService $accessService
  ) {}

  public function index()
  {
    $companyId = $this->accessService->currentCompanyId();

    $roles = $this->accessService->getAvailableRolesForCompany($companyId);
    $permissions = $this->accessService->getAvailablePermissions();

    $divisions = Schema::hasTable('divisions')
      ? Division::query()->where('company_id', $companyId)->active()->orderBy('name')->get()
      : collect();

    $positions = Schema::hasTable('positions')
      ? Position::query()->where('company_id', $companyId)->active()->orderBy('name')->get()
      : collect();

    // View akses pengguna disimpan pada folder auth agar sejalan dengan
    // halaman manajemen user lainnya. Nama view harus sesuai lokasi file
    // agar menu System > Akses Pengguna tidak menghasilkan error 500.
    return view('auth.user_access_index', compact(
      'roles',
      'permissions',
      'divisions',
      'positions'
    ));
  }

  public function data(Request $request): JsonResponse
  {
    $companyId = $this->accessService->currentCompanyId();

    $query = User::query()
      ->with(['company', 'division', 'position'])
      ->when(!$this->accessService->isSuperAdmin($request->user()), function ($query) use ($companyId) {
        $query->where(function ($q) use ($companyId) {
          $q->whereHas('companies', function ($companyQuery) use ($companyId) {
            $companyQuery->where('companies.id', $companyId);
          });

          if (Schema::hasColumn('users', 'company_id')) {
            $q->orWhere('company_id', $companyId);
          }
          if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'company_id')) {
            $q->orWhereHas('employee', function ($employeeQuery) use ($companyId) {
              $employeeQuery->where('employees.company_id', $companyId);
            });
          }
        });
      })
      ->latest('id');

    $users = $query->get()->map(function (User $user) use ($companyId) {
      if (function_exists('setPermissionsTeamId')) {
        setPermissionsTeamId($companyId);
      }

      return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'company' => session('company_name') ?: ($user->company?->name ?? '-'),
        'division' => $user->division?->name ?? '-',
        'position' => $user->position?->name ?? '-',
        'roles' => $user->getRoleNames()->values(),
        'status' => $this->statusText($user),
        'is_super_admin' => $this->accessService->isSuperAdmin($user),
      ];
    });

    return response()->json([
      'data' => $users,
    ]);
  }

  public function edit(Request $request, User $user): JsonResponse
  {
    $this->authorizeUserAccess($request, $user);

    $companyId = $this->accessService->currentCompanyId();

    if (function_exists('setPermissionsTeamId')) {
      setPermissionsTeamId($companyId);
    }

    return response()->json([
      'user' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'username' => Schema::hasColumn('users', 'username') ? $user->username : null,
        'division_id' => $user->division_id,
        'position_id' => $user->position_id,
        'status' => $user->status ?? 'active',
        'is_active' => (bool) ($user->is_active ?? true),
      ],
      'roles' => $user->getRoleNames()->values(),
      'permissions' => $user->getDirectPermissions()->pluck('name')->values(),
    ]);
  }

  public function update(Request $request, User $user): JsonResponse
  {
    $this->authorizeUserAccess($request, $user);

    $rules = [
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'email', 'max:255'],
      'username' => ['nullable', 'string', 'max:100'],
      'division_id' => ['nullable', 'integer', Rule::exists('divisions', 'id')->where(fn ($query) => $query->where('company_id', $this->accessService->currentCompanyId()))],
      'position_id' => ['nullable', 'integer', Rule::exists('positions', 'id')->where(fn ($query) => $query->where('company_id', $this->accessService->currentCompanyId()))],
      'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended'])],
      'is_active' => ['nullable', 'boolean'],
    ];
    if (Schema::hasColumn('users', 'email')) {
      $rules['email'][] = Rule::unique('users', 'email')->ignore($user->id);
    }
    if (Schema::hasColumn('users', 'username')) {
      $rules['username'][] = Rule::unique('users', 'username')->ignore($user->id);
    }
    $validated = $request->validate($rules);

    $updates = [];
    foreach (['name', 'email', 'username', 'division_id', 'position_id', 'status', 'is_active'] as $column) {
      if (array_key_exists($column, $validated) && Schema::hasColumn('users', $column)) {
        $updates[$column] = $validated[$column];
      }
    }
    if ($updates) {
      $user->forceFill($updates)->save();
    }

    return response()->json([
      'message' => 'Data user berhasil diperbarui.',
    ]);
  }

  public function resetPassword(Request $request, User $user): JsonResponse
  {
    abort_unless((bool) ($request->user()?->is_developer ?? false), 403, 'Reset password hanya dapat dilakukan Developer.');
    $this->authorizeUserAccess($request, $user);

    $updates = ['password' => Hash::make('12345678')];
    if (Schema::hasColumn('users', 'password_changed_at')) {
      $updates['password_changed_at'] = null;
    }
    if (Schema::hasColumn('users', 'is_locked')) {
      $updates['is_locked'] = false;
    }
    if (Schema::hasColumn('users', 'status')) {
      $updates['status'] = 'active';
    }
    if (Schema::hasColumn('users', 'is_active')) {
      $updates['is_active'] = true;
    }
    $user->forceFill($updates)->save();

    return response()->json([
      'message' => 'Password direset ke 12345678. Pengguna wajib menggantinya setelah login.',
    ]);
  }

  public function assignRole(Request $request, User $user): JsonResponse
  {
    $this->authorizeUserAccess($request, $user);

    $companyId = $this->accessService->currentCompanyId();

    if (function_exists('setPermissionsTeamId')) {
      setPermissionsTeamId($companyId);
    }

    $availableRoles = $this->accessService
      ->getAvailableRolesForCompany($companyId)
      ->pluck('name')
      ->values()
      ->all();

    $validated = $request->validate([
      'roles' => ['nullable', 'array'],
      'roles.*' => ['string', Rule::in($availableRoles)],
    ]);

    $roles = $validated['roles'] ?? [];

    if (!$this->accessService->isSuperAdmin($request->user()) && in_array('super-admin', $roles, true)) {
      abort(403, 'Tidak boleh assign super-admin.');
    }

    $user->syncRoles($roles);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return response()->json([
      'message' => 'Role berhasil diperbarui.',
    ]);
  }

  public function assignPermission(Request $request, User $user): JsonResponse
  {
    $this->authorizeUserAccess($request, $user);

    $companyId = $this->accessService->currentCompanyId();

    if (function_exists('setPermissionsTeamId')) {
      setPermissionsTeamId($companyId);
    }

    $availablePermissions = $this->accessService
      ->getAvailablePermissions()
      ->pluck('name')
      ->values()
      ->all();

    $validated = $request->validate([
      'permissions' => ['nullable', 'array'],
      'permissions.*' => ['string', Rule::in($availablePermissions)],
    ]);

    $user->syncPermissions($validated['permissions'] ?? []);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return response()->json([
      'message' => 'Permission direct berhasil diperbarui.',
    ]);
  }

  private function authorizeUserAccess(Request $request, User $targetUser): void
  {
    $companyId = $this->accessService->currentCompanyId();
    $belongs = $this->accessService->ensureUserBelongsToCompany($targetUser, $companyId);

    if (!$belongs && Schema::hasTable('employees') && Schema::hasColumn('employees', 'company_id')) {
      $belongs = $targetUser->employee()
        ->where('employees.company_id', $companyId)
        ->exists();
    }
    if (!$belongs) {
      abort(403, 'User bukan bagian dari company aktif.');
    }

    if (
      $this->accessService->isSuperAdmin($targetUser)
      && !$this->accessService->isSuperAdmin($request->user())
    ) {
      abort(403, 'Tidak boleh mengubah super-admin.');
    }
  }

  private function statusText(User $user): string
  {
    if (Schema::hasColumn('users', 'is_locked') && $user->is_locked) {
      return 'locked';
    }

    if (Schema::hasColumn('users', 'status')) {
      return $user->status ?: 'active';
    }

    if (Schema::hasColumn('users', 'is_active')) {
      return $user->is_active ? 'active' : 'inactive';
    }

    return 'active';
  }
}

