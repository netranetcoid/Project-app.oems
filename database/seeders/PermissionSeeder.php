<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            'dashboard.view',

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            'company.view',
            'company.create',
            'company.update',
            'company.delete',

            /*
            |--------------------------------------------------------------------------
            | Branch
            |--------------------------------------------------------------------------
            */

            'branch.view',
            'branch.create',
            'branch.update',
            'branch.delete',

            /*
            |--------------------------------------------------------------------------
            | Division
            |--------------------------------------------------------------------------
            */

            'division.view',
            'division.create',
            'division.update',
            'division.delete',

            /*
            |--------------------------------------------------------------------------
            | Position
            |--------------------------------------------------------------------------
            */

            'position.view',
            'position.create',
            'position.update',
            'position.delete',

            /*
            |--------------------------------------------------------------------------
            | Employee
            |--------------------------------------------------------------------------
            */

            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',

            // Dokumen pegawai mengandung identitas pribadi; permission ini
            // terpisah dari employees.view agar akses dapat dibatasi ketat.
            'employee-document.view',
            'employee-document.manage',


            
            /*
            |--------------------------------------------------------------------------
            | Contract
            |--------------------------------------------------------------------------
            */

            'contract-type.view',
            'contract-type.create',
            'contract-type.update',
            'contract-type.delete',

            /*
            |--------------------------------------------------------------------------
            | Master Document & BPJS Readiness
            |--------------------------------------------------------------------------
            */
            'company-document.view',
            'company-document.create',
            'company-document.update',
            'company-document.delete',
            'bpjs-registration.view',
            'bpjs-registration.manage',
            'bpjs-calculation.view',
            'bpjs-calculation.manage',

            /*
            |--------------------------------------------------------------------------
            | Attendance
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Leave
            |--------------------------------------------------------------------------
            */

            'leave.view',
            'leave.create',
            'leave.update',
            'leave.approve',

            'hr-request.view',
            'hr-request.approve',
            'hr-request.policy',

            /*
            |--------------------------------------------------------------------------
            | Overtime
            |--------------------------------------------------------------------------
            */

            'overtime.view',
            'overtime.create',
            'overtime.update',
            'overtime.approve',

            /*
            |--------------------------------------------------------------------------
            | KPI
            |--------------------------------------------------------------------------
            */

            'kpi.view',
            'kpi.create',
            'kpi.update',
            'kpi.delete',
            'kpi.approve',

            /*
            |--------------------------------------------------------------------------
            | Payroll
            |--------------------------------------------------------------------------
            */

            'payroll.view',
            'payroll.create',
            'payroll.update',
            'payroll.approve',
            'payroll.publish',

            // Read-only pusat laporan lintas payroll dan biaya pegawai.
            'employee-cost.view',

            // Mobile Release Center: hanya developer/super admin/owner yang
            // dapat mempublikasikan APK atau mengubah feature toggle.
            'mobile-release.view',
            'mobile-release.manage',

            'business-trip.view',
            'business-trip.manage',
            'business-trip.approve',
            'vehicle-cost.view',
            'vehicle-cost.manage',
            'vehicle-cost.approve',

            /*
            |--------------------------------------------------------------------------
            | Task
            |--------------------------------------------------------------------------
            */

            'task.view',
            'task.create',
            'task.update',
            'task.delete',
            'task.approve',

            /*
            |--------------------------------------------------------------------------
            | Project
            |--------------------------------------------------------------------------
            */

            'project.view',
            'project.create',
            'project.update',
            'project.delete',

            /*
            |--------------------------------------------------------------------------
            | Asset
            |--------------------------------------------------------------------------
            */

            'asset.view',
            'asset.create',
            'asset.update',
            'asset.delete',

            /*
            |--------------------------------------------------------------------------
            | Knowledge
            |--------------------------------------------------------------------------
            */

            'knowledge.view',
            'knowledge.create',
            'knowledge.update',
            'knowledge.delete',

            /*
            |--------------------------------------------------------------------------
            | Meeting
            |--------------------------------------------------------------------------
            */

            'meeting.view',
            'meeting.create',
            'meeting.update',
            'meeting.delete',

            /*
            |--------------------------------------------------------------------------
            | Report
            |--------------------------------------------------------------------------
            */

            'report.view',
            'report.export',

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            /*
            |--------------------------------------------------------------------------
            | Role
            |--------------------------------------------------------------------------
            */

            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            /*
            |--------------------------------------------------------------------------
            | Permission
            |--------------------------------------------------------------------------
            */

            'permissions.view',
            'permissions.update',

            /*
            |--------------------------------------------------------------------------
            | Menu
            |--------------------------------------------------------------------------
            */

            'menus.view',
            'menus.update',

            /*
            |--------------------------------------------------------------------------
            | Integration, Audit, and Health
            |--------------------------------------------------------------------------
            */

            'integration.view',
            'integration.manage',
            'integration.dispatch',
            'audit.view',
            'health.view',

        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate(

                [

                    'name' => $permission,

                    'guard_name' => 'web',

                ]

            );

        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
