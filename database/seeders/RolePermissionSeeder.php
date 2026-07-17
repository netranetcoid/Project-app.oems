<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    private string $guard = 'web';

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $companies = Schema::hasTable('companies')
            ? Company::query()->active()->get()
            : collect();

        if ($companies->isEmpty()) {

            $this->seedRolesForCompany(null);

        } else {

            foreach ($companies as $company) {

                $this->seedRolesForCompany(
                    (int) $company->id
                );

            }

        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedRolesForCompany(?int $companyId): void
    {
        if (function_exists('setPermissionsTeamId')) {

            setPermissionsTeamId($companyId);

        }

        foreach ($this->rolePermissions() as $roleName => $permissions) {

            $attributes = [

                'name' => $roleName,

                'guard_name' => $this->guard,

            ];

            if (Schema::hasColumn('roles', 'company_id')) {

                $attributes['company_id'] = $companyId;

            }

            $role = Role::firstOrCreate($attributes);

            if ($permissions === ['*']) {

                $role->syncPermissions(
                    Permission::all()
                );

            } else {

                $role->syncPermissions(
                    Permission::whereIn(
                        'name',
                        $permissions
                    )->get()
                );

            }

        }
    }
    private function rolePermissions(): array
{
    return [

        /*
        |--------------------------------------------------------------------------
        | Developer
        |--------------------------------------------------------------------------
        */

        'developer' => ['*'],

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        'super-admin' => ['*'],

        /*
        |--------------------------------------------------------------------------
        | Owner
        |--------------------------------------------------------------------------
        */

        'owner' => ['*'],

        /*
        |--------------------------------------------------------------------------
        | General Manager
        |--------------------------------------------------------------------------
        */

        'general-manager' => [

            'dashboard.view',

            'company.view',

            'branch.view',

            'division.view',

            'position.view',

            'employees.view',

            'attendance.view',
            'attendance.shift.view',
            'attendance.shift.assignment.view',

            'leave.view',
            'hr-request.view',

            'overtime.view',

            'kpi.view',

            'payroll.view',

            'business-trip.view',
            'vehicle-cost.view',

            'task.view',

            'project.view',

            'asset.view',

            'knowledge.view',

            'meeting.view',

            'report.view',

            'report.export',

            'integration.view',
            'audit.view',
            'health.view',

        ],

        /*
        |--------------------------------------------------------------------------
        | HR
        |--------------------------------------------------------------------------
        */

        'hr' => [

            'dashboard.view',

            'branch.view',

            'division.view',

            'position.view',

            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',

            'contract-type.view',
            'contract-type.create',
            'contract-type.update',
            'contract-type.delete',

            'attendance.view',
            'attendance.create',
            'attendance.update',
            'attendance.delete',
            'attendance.shift.view',
            'attendance.shift.create',
            'attendance.shift.update',
            'attendance.shift.delete',
            'attendance.shift.assignment.view',
            'attendance.shift.assignment.create',
            'attendance.shift.assignment.update',
            'attendance.shift.assignment.delete',

            'leave.view',
            'leave.create',
            'leave.update',
            'leave.approve',

            'hr-request.view',
            'hr-request.approve',
            'hr-request.policy',

            'business-trip.view',
            'business-trip.manage',
            'business-trip.approve',
            'vehicle-cost.view',
            'vehicle-cost.manage',
            'vehicle-cost.approve',

            'overtime.view',
            'overtime.create',
            'overtime.update',
            'overtime.approve',

            'kpi.view',
            'kpi.create',
            'kpi.update',
            'kpi.approve',

            'payroll.view',
            'payroll.create',
            'payroll.update',
            'payroll.approve',
            'payroll.publish',

            'report.view',

            'integration.view',
            'audit.view',
            'health.view',

        ],

        /*
        |--------------------------------------------------------------------------
        | Finance
        |--------------------------------------------------------------------------
        */

        'finance' => [

            'dashboard.view',

            'payroll.view',
            'payroll.create',
            'payroll.update',
            'payroll.approve',

            'report.view',
            'report.export',

            'integration.view',
            'health.view',

        ],

        /*
        |--------------------------------------------------------------------------
        | NOC
        |--------------------------------------------------------------------------
        */

        'noc' => [

            'dashboard.view',

            'employees.view',

            'attendance.view',

            'task.view',
            'task.create',
            'task.update',

            'project.view',

            'knowledge.view',

            'report.view',

        ],

        /*
        |--------------------------------------------------------------------------
        | Technician
        |--------------------------------------------------------------------------
        */

        'technician' => [

            'dashboard.view',

            'attendance.view',

            'task.view',
            'task.update',

            'knowledge.view',

        ],

        /*
        |--------------------------------------------------------------------------
        | Marketing
        |--------------------------------------------------------------------------
        */

        'marketing' => [

            'dashboard.view',

            'project.view',

            'task.view',

            'report.view',

        ],

        /*
        |--------------------------------------------------------------------------
        | Viewer
        |--------------------------------------------------------------------------
        */

        'viewer' => [

            'dashboard.view',

        ],

    ];
}
}
