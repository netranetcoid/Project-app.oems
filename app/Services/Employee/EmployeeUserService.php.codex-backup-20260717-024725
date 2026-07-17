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
        return User::create([

            'company_id' => session('company_id'),

            'branch_id' => $data['branch_id'] ?? null,

            'division_id' => $data['division_id'] ?? null,

            'position_id' => $data['position_id'] ?? null,

            // Tautkan akun mobile dengan master karyawan setelah employee
            // berhasil dibuat dalam transaksi yang sama.
            'employee_id' => $data['employee_id'] ?? null,

            'name' => $data['name'],

            'username' => $data['username'] ?? $data['email'],

            'email' => $data['email'],

            'phone' => $data['phone'] ?? null,

            /*
            |--------------------------------------------------------------------------
            | Default Password
            |--------------------------------------------------------------------------
            */

            'password' => Hash::make('12345678'),

            'status' => 'active',

            'is_active' => true,

            'is_locked' => false,

            'is_super_admin' => false,

            'is_owner' => false,

        ]);
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

        $user->update([

            'branch_id' => $data['branch_id'] ?? $user->branch_id,

            'division_id' => $data['division_id'] ?? $user->division_id,

            'position_id' => $data['position_id'] ?? $user->position_id,

            'name' => $data['name'],

            'email' => $data['email'],

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

            'password_changed_at' => now(),

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
