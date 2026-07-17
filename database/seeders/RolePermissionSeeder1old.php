<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder1 extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            /*
            |--------------------------------------------------------------------------
            | COMPANY
            |--------------------------------------------------------------------------
            */

            DB::table('companies')->updateOrInsert(

                [
                    'code' => 'OEMS',
                ],

                [
                    'name' => 'OEMS',
                    'legal_name' => 'OEMS',
                    'brand_name' => 'OEMS',
                    'business_type' => 'ISP',
                    'industry_type' => 'Telecommunication',
                    'email' => 'developer@oems.local',
                    'timezone' => 'Asia/Jakarta',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]

            );

            $company = DB::table('companies')
                ->where('code', 'OEMS')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | BRANCH (SITE)
            |--------------------------------------------------------------------------
            */

            DB::table('branches')->updateOrInsert(

                [
                    'company_id' => $company->id,
                    'code'       => 'HO',
                ],

                [
                    'name'       => 'Head Office',
                    'type'       => 'head_office',
                    'timezone'   => 'Asia/Jakarta',
                    'status'     => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]

            );

            $branch = DB::table('branches')

                ->where('company_id', $company->id)

                ->where('code', 'HO')

                ->first();

            /*
            |--------------------------------------------------------------------------
            | DIVISION
            |--------------------------------------------------------------------------
            */
                        $divisions = [

                [
                    'code' => 'MANAGEMENT',
                    'name' => 'Management',
                    'type' => 'management',
                ],

                [
                    'code' => 'HR',
                    'name' => 'Human Resource',
                    'type' => 'hr',
                ],

                [
                    'code' => 'FINANCE',
                    'name' => 'Finance',
                    'type' => 'finance',
                ],

                [
                    'code' => 'NOC',
                    'name' => 'Network Operation Center',
                    'type' => 'noc',
                ],

                [
                    'code' => 'TEKNISI',
                    'name' => 'Teknisi',
                    'type' => 'teknisi',
                ],

                [
                    'code' => 'MARKETING',
                    'name' => 'Marketing',
                    'type' => 'marketing',
                ],

                [
                    'code' => 'SALES',
                    'name' => 'Sales',
                    'type' => 'sales',
                ],

                [
                    'code' => 'CUSTOMER_SERVICE',
                    'name' => 'Customer Service',
                    'type' => 'cs',
                ],

                [
                    'code' => 'ADMIN',
                    'name' => 'Administration',
                    'type' => 'admin',
                ],

                [
                    'code' => 'WAREHOUSE',
                    'name' => 'Warehouse',
                    'type' => 'warehouse',
                ],

                [
                    'code' => 'PROCUREMENT',
                    'name' => 'Procurement',
                    'type' => 'procurement',
                ],

                [
                    'code' => 'PROJECT',
                    'name' => 'Project',
                    'type' => 'project',
                ],

                [
                    'code' => 'IT',
                    'name' => 'Information Technology',
                    'type' => 'it',
                ],

            ];

            foreach ($divisions as $division) {

                DB::table('divisions')->updateOrInsert(

                    [
                        'company_id' => $company->id,
                        'code'       => $division['code'],
                    ],

                    [

                        'name'                     => $division['name'],

                        'type'                     => $division['type'],

                        'status'                   => 'active',

                        'is_kpi_enabled'           => true,

                        'is_payroll_enabled'       => true,

                        'is_attendance_required'   => true,

                        'created_at'               => now(),

                        'updated_at'               => now(),

                    ]

                );

            }

            $managementDivision = DB::table('divisions')

                ->where('company_id', $company->id)

                ->where('code', 'MANAGEMENT')

                ->first();

            /*
            |--------------------------------------------------------------------------
            | POSITION
            |--------------------------------------------------------------------------
            */
                        $positions = [

                // MANAGEMENT

                [
                    'division' => 'MANAGEMENT',
                    'code' => 'OWNER',
                    'name' => 'Owner',
                    'level' => 100,
                    'type' => 'owner',
                    'is_management' => true,
                    'is_approver' => true,
                ],

                [
                    'division' => 'MANAGEMENT',
                    'code' => 'DIRECTOR',
                    'name' => 'Director',
                    'level' => 90,
                    'type' => 'director',
                    'is_management' => true,
                    'is_approver' => true,
                ],

                [
                    'division' => 'MANAGEMENT',
                    'code' => 'GENERAL_MANAGER',
                    'name' => 'General Manager',
                    'level' => 80,
                    'type' => 'manager',
                    'is_management' => true,
                    'is_approver' => true,
                ],

                [
                    'division' => 'MANAGEMENT',
                    'code' => 'MANAGER',
                    'name' => 'Manager',
                    'level' => 70,
                    'type' => 'manager',
                    'is_management' => true,
                    'is_approver' => true,
                ],

                [
                    'division' => 'MANAGEMENT',
                    'code' => 'SUPERVISOR',
                    'name' => 'Supervisor',
                    'level' => 60,
                    'type' => 'supervisor',
                    'is_management' => true,
                    'is_approver' => true,
                ],

                // HR

                [
                    'division' => 'HR',
                    'code' => 'HR_MANAGER',
                    'name' => 'HR Manager',
                    'level' => 60,
                    'type' => 'manager',
                    'is_management' => true,
                    'is_approver' => true,
                ],

                [
                    'division' => 'HR',
                    'code' => 'HR_STAFF',
                    'name' => 'HR Staff',
                    'level' => 20,
                    'type' => 'staff',
                    'is_management' => false,
                    'is_approver' => false,
                ],

                // FINANCE

                [
                    'division' => 'FINANCE',
                    'code' => 'FINANCE_MANAGER',
                    'name' => 'Finance Manager',
                    'level' => 60,
                    'type' => 'manager',
                    'is_management' => true,
                    'is_approver' => true,
                ],

                [
                    'division' => 'FINANCE',
                    'code' => 'FINANCE_STAFF',
                    'name' => 'Finance Staff',
                    'level' => 20,
                    'type' => 'staff',
                    'is_management' => false,
                    'is_approver' => false,
                ],

                // NOC

                [
                    'division' => 'NOC',
                    'code' => 'NOC_LEADER',
                    'name' => 'Leader NOC',
                    'level' => 40,
                    'type' => 'leader',
                    'is_management' => false,
                    'is_approver' => true,
                ],

                [
                    'division' => 'NOC',
                    'code' => 'NOC_STAFF',
                    'name' => 'NOC Staff',
                    'level' => 20,
                    'type' => 'staff',
                    'is_management' => false,
                    'is_approver' => false,
                ],

                // TEKNISI

                [
                    'division' => 'TEKNISI',
                    'code' => 'LEADER_TEKNISI',
                    'name' => 'Leader Teknisi',
                    'level' => 40,
                    'type' => 'leader',
                    'is_management' => false,
                    'is_approver' => true,
                    'is_field_worker' => true,
                ],

                [
                    'division' => 'TEKNISI',
                    'code' => 'TEKNISI',
                    'name' => 'Teknisi',
                    'level' => 20,
                    'type' => 'staff',
                    'is_management' => false,
                    'is_approver' => false,
                    'is_field_worker' => true,
                ],

                // MARKETING

                [
                    'division' => 'MARKETING',
                    'code' => 'MARKETING_MANAGER',
                    'name' => 'Marketing Manager',
                    'level' => 60,
                    'type' => 'manager',
                    'is_management' => true,
                    'is_approver' => true,
                ],

                [
                    'division' => 'MARKETING',
                    'code' => 'MARKETING_STAFF',
                    'name' => 'Marketing Staff',
                    'level' => 20,
                    'type' => 'staff',
                    'is_management' => false,
                    'is_approver' => false,
                    'is_field_worker' => true,
                ],

                // SALES

                [
                    'division' => 'SALES',
                    'code' => 'SALES_STAFF',
                    'name' => 'Sales',
                    'level' => 20,
                    'type' => 'staff',
                    'is_management' => false,
                    'is_approver' => false,
                    'is_field_worker' => true,
                ],

                // CUSTOMER SERVICE

                [
                    'division' => 'CUSTOMER_SERVICE',
                    'code' => 'CS_STAFF',
                    'name' => 'Customer Service',
                    'level' => 20,
                    'type' => 'staff',
                    'is_management' => false,
                    'is_approver' => false,
                ],

            ];

            foreach ($positions as $position) {

                $division = DB::table('divisions')

                    ->where('company_id', $company->id)

                    ->where('code', $position['division'])

                    ->first();

                DB::table('positions')->updateOrInsert(

                    [

                        'company_id' => $company->id,

                        'code' => $position['code'],

                    ],

                    [

                        'division_id' => $division?->id,

                        'name' => $position['name'],

                        'level' => $position['level'],

                        'type' => $position['type'],

                        'status' => 'active',

                        'is_management' => $position['is_management'],

                        'is_approver' => $position['is_approver'],

                        'is_field_worker' => $position['is_field_worker'] ?? false,

                        'is_kpi_enabled' => true,

                        'is_payroll_enabled' => true,

                        'created_at' => now(),

                        'updated_at' => now(),

                    ]

                );

            }

            $ownerPosition = DB::table('positions')

                ->where('company_id', $company->id)

                ->where('code', 'OWNER')

                ->first();

            /*
            |--------------------------------------------------------------------------
            | MODULE
            |--------------------------------------------------------------------------
            */
                        $modules = [

                ['code'=>'dashboard','name'=>'Dashboard','group'=>'core','icon'=>'ti ti-dashboard','sort'=>1],

                ['code'=>'master','name'=>'Master Data','group'=>'core','icon'=>'ti ti-database','sort'=>2],

                ['code'=>'employee','name'=>'Employee','group'=>'hr','icon'=>'ti ti-users','sort'=>3],

                ['code'=>'attendance','name'=>'Attendance','group'=>'hr','icon'=>'ti ti-clock','sort'=>4],

                ['code'=>'leave','name'=>'Leave','group'=>'hr','icon'=>'ti ti-calendar-off','sort'=>5],

                ['code'=>'overtime','name'=>'Overtime','group'=>'hr','icon'=>'ti ti-clock-hour-8','sort'=>6],

                ['code'=>'payroll','name'=>'Payroll','group'=>'finance','icon'=>'ti ti-cash','sort'=>7],

                ['code'=>'kpi','name'=>'KPI','group'=>'performance','icon'=>'ti ti-chart-bar','sort'=>8],

                ['code'=>'task','name'=>'Task Management','group'=>'operation','icon'=>'ti ti-checklist','sort'=>9],

                ['code'=>'ticket','name'=>'Ticket','group'=>'operation','icon'=>'ti ti-ticket','sort'=>10],

                ['code'=>'project','name'=>'Project','group'=>'operation','icon'=>'ti ti-briefcase','sort'=>11],

                ['code'=>'asset','name'=>'Asset','group'=>'operation','icon'=>'ti ti-device-laptop','sort'=>12],

                ['code'=>'knowledge','name'=>'Knowledge','group'=>'operation','icon'=>'ti ti-book','sort'=>13],

                ['code'=>'meeting','name'=>'Meeting','group'=>'operation','icon'=>'ti ti-users-group','sort'=>14],

                ['code'=>'approval','name'=>'Approval','group'=>'workflow','icon'=>'ti ti-checkup-list','sort'=>15],

                ['code'=>'report','name'=>'Report','group'=>'report','icon'=>'ti ti-report','sort'=>16],

                ['code'=>'setting','name'=>'Setting','group'=>'system','icon'=>'ti ti-settings','sort'=>17],

                ['code'=>'audit','name'=>'Audit Log','group'=>'system','icon'=>'ti ti-history','sort'=>18],

            ];

            foreach ($modules as $module) {

                DB::table('modules')->updateOrInsert(

                    [

                        'code'=>$module['code'],

                    ],

                    [

                        'name'=>$module['name'],

                        'label'=>$module['name'],

                        'group'=>$module['group'],

                        'icon'=>$module['icon'],

                        'sort_order'=>$module['sort'],

                        'is_active'=>true,

                        'is_visible'=>true,

                        'is_system'=>true,

                        'created_at'=>now(),

                        'updated_at'=>now(),

                    ]

                );

            }

            /*
            |--------------------------------------------------------------------------
            | PERMISSION
            |--------------------------------------------------------------------------
            |
            | Format :
            |
            | module.resource.action
            |
            |--------------------------------------------------------------------------
            */

            $resources = [

                'dashboard'=>[
                    'dashboard',
                ],

                'master'=>[
                    'company',
                    'branch',
                    'division',
                    'position',
                ],

                'employee'=>[
                    'employees',
                    'employee-documents',
                    'employee-contracts',
                ],

                'attendance'=>[
                    'attendance',
                    'attendance.shift',
                    'attendance.shift.assignment',
                    'leave',
                    'overtime',
                ],

                'payroll'=>[
                    'payroll',
                    'salary-components',
                    'employee-salaries',
                    'loans',
                    'reimbursements',
                ],

                'kpi'=>[
                    'kpi',
                    'kpi-periods',
                    'kpi-indicators',
                    'kpi-rules',
                    'kpi-appeals',
                ],

                'task'=>[
                    'tasks',
                    'task-categories',
                ],

                'ticket'=>[
                    'tickets',
                ],

                'project'=>[
                    'projects',
                ],

                'asset'=>[
                    'assets',
                ],

                'knowledge'=>[
                    'knowledge',
                ],

                'meeting'=>[
                    'meetings',
                ],

                'approval'=>[
                    'approvals',
                    'approval-flows',
                ],

                'report'=>[
                    'reports',
                ],

                'setting'=>[
                    'users',
                    'roles',
                    'permissions',
                    'menus',
                    'modules',
                    'settings',
                ],

                'audit'=>[
                    'audit-logs',
                ],

            ];

            $actions = [

                'view',

                'create',

                'update',

                'delete',

                'restore',

                'approve',

                'reject',

                'export',

                'import',

                'print',

                'lock',

                'unlock',

            ];

            foreach ($resources as $items) {

                foreach ($items as $resource) {

                    foreach ($actions as $action) {

                        Permission::firstOrCreate(

                            [

                                'company_id'=>$company->id,

                                'name'=>$resource.'.'.$action,

                                'guard_name'=>'web',

                            ]

                        );

                    }

                }

            }

            Permission::firstOrCreate(

                [

                    'company_id'=>$company->id,

                    'name'=>'dashboard.view',

                    'guard_name'=>'web',

                ]

            );

                        /*
            |--------------------------------------------------------------------------
            | ROLE
            |--------------------------------------------------------------------------
            */

            $roles = [

                'super-admin',

                'owner',

                'director',

                'general-manager',

                'manager',

                'supervisor',

                'hr',

                'finance',

                'leader-teknisi',

                'teknisi',

                'noc',

                'marketing',

                'sales',

                'customer-service',

                'admin',

                'staff',

            ];

            foreach ($roles as $roleName) {

                Role::firstOrCreate(

                    [

                        'company_id' => $company->id,

                        'name' => $roleName,

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

                    Permission::pluck('name')->toArray()

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

                    Permission::pluck('name')->toArray()

                );

            }

            /*
            |--------------------------------------------------------------------------
            | DIRECTOR
            |--------------------------------------------------------------------------
            */

            $director = Role::where(

                'company_id',

                $company->id

            )

            ->where(

                'name',

                'director'

            )

            ->first();

            if ($director) {

                $director->syncPermissions(

                    Permission::pluck('name')->toArray()

                );

            }

            /*
            |--------------------------------------------------------------------------
            | MENU
            |--------------------------------------------------------------------------
            */
                        $menus = [

                /*
                |--------------------------------------------------------------------------
                | Dashboard
                |--------------------------------------------------------------------------
                */

                [
                    'module'     => 'dashboard',
                    'code'       => 'dashboard',
                    'name'       => 'Dashboard',
                    'route'      => 'dashboard',
                    'url'        => '/',
                    'icon'       => 'ti ti-dashboard',
                    'permission' => 'dashboard.view',
                    'sort'       => 1,
                    'parent'     => null,
                ],

                /*
                |--------------------------------------------------------------------------
                | MASTER
                |--------------------------------------------------------------------------
                */

                [
                    'module'     => 'master',
                    'code'       => 'master',
                    'name'       => 'Master Data',
                    'route'      => null,
                    'url'        => '#',
                    'icon'       => 'ti ti-database',
                    'permission' => null,
                    'sort'       => 10,
                    'parent'     => null,
                ],

                [
                    'module'     => 'master',
                    'code'       => 'branch',
                    'name'       => 'Site / Branch',
                    'route'      => 'master.branches.index',
                    'url'        => '/master/branches',
                    'icon'       => 'ti ti-building-community',
                    'permission' => 'branch.view',
                    'sort'       => 11,
                    'parent'     => 'master',
                ],

                [
                    'module'     => 'master',
                    'code'       => 'division',
                    'name'       => 'Division',
                    'route'      => 'master.divisions.index',
                    'url'        => '/master/divisions',
                    'icon'       => 'ti ti-sitemap',
                    'permission' => 'division.view',
                    'sort'       => 12,
                    'parent'     => 'master',
                ],

                [
                    'module'     => 'master',
                    'code'       => 'position',
                    'name'       => 'Position',
                    'route'      => 'master.positions.index',
                    'url'        => '/master/positions',
                    'icon'       => 'ti ti-user-star',
                    'permission' => 'position.view',
                    'sort'       => 13,
                    'parent'     => 'master',
                ],

                /*
                |--------------------------------------------------------------------------
                | HR
                |--------------------------------------------------------------------------
                */

                [
                    'module'     => 'employee',
                    'code'       => 'employees',
                    'name'       => 'Data Pegawai',
                    'route'      => 'employees.index',
                    'url'        => '/employees',
                    'icon'       => 'ti ti-users',
                    'permission' => 'employees.view',
                    'sort'       => 20,
                    'parent'     => null,
                ],

                [
                    'module'     => 'attendance',
                    'code'       => 'attendance',
                    'name'       => 'Absensi',
                    'route'      => 'attendance.index',
                    'url'        => '/attendance',
                    'icon'       => 'ti ti-clock',
                    'permission' => 'attendance.view',
                    'sort'       => 30,
                    'parent'     => null,
                ],

                [
                    'module'     => 'attendance',
                    'code'       => 'attendance-shifts',
                    'name'       => 'Master Shift',
                    'route'      => 'attendance.shifts.index',
                    'url'        => '/attendance/shifts',
                    'icon'       => 'ti ti-clock-hour-8',
                    'permission' => 'attendance.shift.view',
                    'sort'       => 31,
                    'parent'     => 'attendance',
                ],

                [
                    'module'     => 'attendance',
                    'code'       => 'attendance-shift-assignment',
                    'name'       => 'Jadwal Shift',
                    'route'      => 'attendance.shift-assignments.index',
                    'url'        => '/attendance/shift-assignments',
                    'icon'       => 'ti ti-calendar-event',
                    'permission' => 'attendance.shift.assignment.view',
                    'sort'       => 32,
                    'parent'     => 'attendance',
                ],

                /*
                |--------------------------------------------------------------------------
                | KPI
                |--------------------------------------------------------------------------
                */

                [
                    'module'     => 'kpi',
                    'code'       => 'kpi',
                    'name'       => 'KPI',
                    'route'      => 'kpi.index',
                    'url'        => '/kpi',
                    'icon'       => 'ti ti-chart-bar',
                    'permission' => 'kpi.view',
                    'sort'       => 40,
                    'parent'     => null,
                ],

                /*
                |--------------------------------------------------------------------------
                | Payroll
                |--------------------------------------------------------------------------
                */

                [
                    'module'     => 'payroll',
                    'code'       => 'payroll',
                    'name'       => 'Payroll',
                    'route'      => 'payroll.index',
                    'url'        => '/payroll',
                    'icon'       => 'ti ti-cash',
                    'permission' => 'payroll.view',
                    'sort'       => 50,
                    'parent'     => null,
                ],

                /*
                |--------------------------------------------------------------------------
                | SYSTEM
                |--------------------------------------------------------------------------
                */

                [
                    'module'     => 'setting',
                    'code'       => 'settings',
                    'name'       => 'Settings',
                    'route'      => null,
                    'url'        => '#',
                    'icon'       => 'ti ti-settings',
                    'permission' => null,
                    'sort'       => 90,
                    'parent'     => null,
                ],

                [
                    'module'     => 'setting',
                    'code'       => 'users',
                    'name'       => 'User Login',
                    'route'      => 'settings.users.index',
                    'url'        => '/settings/users',
                    'icon'       => 'ti ti-user-cog',
                    'permission' => 'users.view',
                    'sort'       => 91,
                    'parent'     => 'settings',
                ],

                [
                    'module'     => 'setting',
                    'code'       => 'roles',
                    'name'       => 'Role & Permission',
                    'route'      => 'settings.roles.index',
                    'url'        => '/settings/roles',
                    'icon'       => 'ti ti-shield-lock',
                    'permission' => 'roles.view',
                    'sort'       => 92,
                    'parent'     => 'settings',
                ],

            ];

            foreach ($menus as $menu) {

                $module = DB::table('modules')
                    ->where('code', $menu['module'])
                    ->first();

                $parentId = null;

                if (!empty($menu['parent'])) {

                    $parentId = DB::table('menus')
                        ->where('code', $menu['parent'])
                        ->value('id');

                }

                DB::table('menus')->updateOrInsert(

                    [
                        'code' => $menu['code'],
                    ],

                    [

                        'module_id'       => $module?->id,

                        'parent_id'       => $parentId,

                        'name'            => $menu['name'],

                        'label'           => $menu['name'],

                        'type'            => 'menu',

                        'icon'            => $menu['icon'],

                        'url'             => $menu['url'],

                        'route_name'      => $menu['route'],

                        'permission_name' => $menu['permission'],

                        'sort_order'      => $menu['sort'],

                        'level'           => $parentId ? 2 : 1,

                        'is_active'       => true,

                        'is_visible'      => true,

                        'is_system'       => true,

                        'created_at'      => now(),

                        'updated_at'      => now(),

                    ]

                );

            }
            /*
            |--------------------------------------------------------------------------
            | DEVELOPER USER
            |--------------------------------------------------------------------------
            */

            $user = User::updateOrCreate(

                [
                    'email' => 'developer@oems.local',
                ],

                [

                    'company_id' => $company->id,

                    'name' => 'Developer',

                    'email' => 'developer@oems.local',

                    'password' => Hash::make('12345678'),

                    'status' => 'active',

                    'is_active' => true,

                    'is_locked' => false,

                    'is_super_admin' => true,

                    'is_owner' => true,

                    'is_developer' => true,

                    'email_verified_at' => now(),

                ]

            );

            /*
            |--------------------------------------------------------------------------
            | ASSIGN ROLE
            |--------------------------------------------------------------------------
            */

            $role = Role::where('company_id', $company->id)

                ->where('name', 'super-admin')

                ->first();

            if ($role) {

                DB::table('model_has_roles')

                    ->updateOrInsert(

                        [

                            'role_id' => $role->id,

                            'model_type' => User::class,

                            'model_id' => $user->id,

                            'company_id' => $company->id,

                        ],

                        []

                    );

            }

            /*
            |--------------------------------------------------------------------------
            | CLEAR CACHE
            |--------------------------------------------------------------------------
            */

            app(PermissionRegistrar::class)

                ->forgetCachedPermissions();

            $this->command->info('');
            $this->command->info('=========================================');
            $this->command->info('   OEMS INITIAL DATA CREATED');
            $this->command->info('=========================================');
            $this->command->line('Company : OEMS');
            $this->command->line('User    : developer@oems.local');
            $this->command->line('Password: 12345678');
            $this->command->info('=========================================');

        });

    }

}