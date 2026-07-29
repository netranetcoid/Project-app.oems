<?php

namespace App\Services\Employee;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeUserService
{
    /*
    |--------------------------------------------------------------------------
    | Create Login User
    |--------------------------------------------------------------------------
    */

    public function create(array $data): User
    {
        $companyId = (int) ($data['company_id'] ?? session('company_id'));
        $email = trim((string) ($data['email'] ?? ''));

        if ($companyId <= 0 || $email === '') {
            throw new \RuntimeException('Company dan email pegawai wajib ada sebelum akun OvallHR dibuat.');
        }

        // withTrashed mencegah email lama yang pernah dihapus membuat error unik.
        $user = User::withTrashed()->where('email', $email)->first();
        $isNew = ! $user;

        if ($user && $user->company_id && (int) $user->company_id !== $companyId) {
            throw new \RuntimeException('Email ini sudah dipakai akun pada perusahaan lain.');
        }

        $user ??= new User();
        if ($user->trashed()) {
            $user->restore();
        }

        $user->fill([
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? null,
            'division_id' => $data['division_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'name' => $data['name'] ?? $data['full_name'] ?? $email,
            'username' => $user->username ?: ($data['employee_no'] ?? $data['employee_code'] ?? $email),
            'email' => $email,
            'phone' => $data['phone'] ?? null,
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
            'locked_at' => null,
            'is_super_admin' => (bool) $user->is_super_admin,
            'is_owner' => (bool) $user->is_owner,
        ]);

        if ($isNew) {
            // Password awal tidak dicatat di log/email sumber. API akan
            // memaksa pegawai menggantinya sebelum membuka menu operasional.
            $user->password = Hash::make('12345678');
            $user->password_changed_at = null;
        }

        $user->save();

        return $user;
    }

    /*
    |--------------------------------------------------------------------------
    | Update Login User
    |--------------------------------------------------------------------------
    */

    public function update(?User $user, array $data): void
    {
        if (!$user) {
            return;
        }

        // Email pada master pegawai adalah email kontak dan boleh berbeda dari
        // AppBill. Jangan mengganti email akun mobile otomatis: identitas
        // tracking tetap memakai user_id + employee_id yang sudah terhubung.
        $user->update([
            'branch_id' => $data['branch_id'] ?? $user->branch_id,
            'division_id' => $data['division_id'] ?? $user->division_id,
            'position_id' => $data['position_id'] ?? $user->position_id,
            'name' => $data['name'] ?? $user->name,
            'phone' => $data['phone'] ?? $user->phone,
        ]);
    }
        /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    */

    public function resetPassword(
        User $user,
        string $password = '12345678'
    ): void {

        $user->update([

            'password' => Hash::make($password),

            'password_changed_at' => null,

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */

    public function changePassword(
        User $user,
        string $password
    ): void {

        $user->update([

            'password' => Hash::make($password),

            'password_changed_at' => now(),

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Activate User
    |--------------------------------------------------------------------------
    */

    public function activate(User $user): void
    {
        $user->update([

            'status' => 'active',

            'is_active' => true,

            'is_locked' => false,

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Deactivate User
    |--------------------------------------------------------------------------
    */

    public function deactivate(User $user): void
    {
        $user->update([

            'status' => 'inactive',

            'is_active' => false,

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Lock User
    |--------------------------------------------------------------------------
    */

    public function lock(User $user): void
    {
        $user->update([

            'is_locked' => true,

            'locked_at' => now(),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Unlock User
    |--------------------------------------------------------------------------
    */

    public function unlock(User $user): void
    {
        $user->update([

            'is_locked' => false,

            'locked_at' => null,

        ]);
    }
}
