<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company = DB::table('companies')
            ->where('code', 'OEMS')
            ->first();

        if (!$company) {
            $this->command->error('Company OEMS tidak ditemukan.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        */

        $branch = DB::table('branches')
            ->where('company_id', $company->id)
            ->where('code', 'HO')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | User Developer
        |--------------------------------------------------------------------------
        */

        $developer = User::updateOrCreate(

            [
                'email' => 'developer@oems.local',
            ],

            [

                'company_id' => $company->id,

                'branch_id' => $branch?->id,

                'name' => 'Developer',

                'username' => 'developer',

                'password' => Hash::make('12345678'),

                'language' => 'id',

                'timezone' => 'Asia/Jakarta',

                'is_super_admin' => true,

                'is_developer' => true,

                'is_active' => true,

                'status' => 'active',

            ]

        );

        // Pastikan akun developer punya akses pivot yang sama dengan
        // users.company_id, sehingga middleware tidak membuat redirect loop.
        DB::table('company_user')->updateOrInsert(
            [
                'company_id' => $company->id,
                'user_id' => $developer->id,
            ],
            [
                'is_default' => true,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Role Super Admin
        |--------------------------------------------------------------------------
        */

        $role = Role::where('company_id', $company->id)
            ->where('name', 'super-admin')
            ->first();

        if (!$role) {

            $this->command->error('Role Super Admin tidak ditemukan.');

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Clear Old Role
        |--------------------------------------------------------------------------
        */

        DB::table('model_has_roles')

            ->where('company_id', $company->id)

            ->where('model_type', User::class)

            ->where('model_id', $developer->id)

            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Assign Role
        |--------------------------------------------------------------------------
        */

        DB::table('model_has_roles')->insert([

            'role_id' => $role->id,

            'model_type' => User::class,

            'model_id' => $developer->id,

            'company_id' => $company->id,

        ]);

        $this->command->info('Developer berhasil dibuat.');

    }
}
