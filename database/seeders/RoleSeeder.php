<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $company = DB::table('companies')
            ->where('code', 'OEMS')
            ->first();

        if (!$company) {
            return;
        }

        $roles = [

            'super-admin',

            'owner',

            'director',

            'general-manager',

            'manager',

            'supervisor',

            'leader',

            'hr',

            'finance',

            'noc',

            'technician',

            'marketing',

            'sales',

            'customer-service',

            'staff',

        ];

        foreach ($roles as $name) {

            Role::firstOrCreate(

                [

                    'company_id' => $company->id,

                    'name' => $name,

                    'guard_name' => 'web',

                ]

            );

        }

        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::where(

            'company_id',

            $company->id

        )

        ->where(

            'name',

            'super-admin'

        )

        ->first();

        if ($superAdmin) {

            $superAdmin->syncPermissions(

                Permission::all()

            );

        }

        /*
        |--------------------------------------------------------------------------
        | OWNER
        |--------------------------------------------------------------------------
        */

        $owner = Role::where(

            'company_id',

            $company->id

        )

        ->where(

            'name',

            'owner'

        )

        ->first();

        if ($owner) {

            $owner->syncPermissions(

                Permission::all()

            );

        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}