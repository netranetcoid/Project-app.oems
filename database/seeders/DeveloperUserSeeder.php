<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DeveloperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();

if (!$company) {

    $company = Company::create([
        'code' => 'DEV',
        'name' => 'Development Company',
    ]);

}

        $user = User::updateOrCreate(
            [
                'email' => 'developer@oems.local',
            ],
            [
                'company_id'      => $company->id,
                'name'            => 'Developer',
                'password'        => Hash::make('12345678'),
                'phone'           => '0000000000',
                'status'          => 'active',
                'is_active'       => true,
                'is_locked'       => false,
                'is_super_admin'  => true,
                'is_owner'        => true,
                'is_developer'    => true,
            ]
        );

        // Pastikan role ada
        $role = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        $user->syncRoles([$role]);

       $this->command->info('');
$this->command->info('====================================');
$this->command->info('Developer Account Created');
$this->command->info('====================================');
$this->command->line('Email    : developer@oems.local');
$this->command->line('Password : 12345678');
$this->command->info('====================================');
    }
}