<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [

            [
                'code' => 'dashboard',
                'name' => 'Dashboard',
                'group' => 'Core',
                'icon' => 'ti ti-dashboard',
                'sort_order' => 1,
            ],

            [
                'code' => 'master',
                'name' => 'Master Data',
                'group' => 'Core',
                'icon' => 'ti ti-database',
                'sort_order' => 2,
            ],

            [
                'code' => 'employee',
                'name' => 'Employee',
                'group' => 'HR',
                'icon' => 'ti ti-users',
                'sort_order' => 3,
            ],

            [
                'code' => 'attendance',
                'name' => 'Attendance',
                'group' => 'HR',
                'icon' => 'ti ti-clock',
                'sort_order' => 4,
            ],

            [
                'code' => 'leave',
                'name' => 'Leave',
                'group' => 'HR',
                'icon' => 'ti ti-calendar-off',
                'sort_order' => 5,
            ],

            [
                'code' => 'overtime',
                'name' => 'Overtime',
                'group' => 'HR',
                'icon' => 'ti ti-clock-hour-8',
                'sort_order' => 6,
            ],

            [
                'code' => 'payroll',
                'name' => 'Payroll',
                'group' => 'HR',
                'icon' => 'ti ti-cash',
                'sort_order' => 7,
            ],

            [
                'code' => 'kpi',
                'name' => 'KPI',
                'group' => 'Performance',
                'icon' => 'ti ti-chart-bar',
                'sort_order' => 8,
            ],

            [
                'code' => 'task',
                'name' => 'Task',
                'group' => 'Operation',
                'icon' => 'ti ti-checklist',
                'sort_order' => 9,
            ],

            [
                'code' => 'project',
                'name' => 'Project',
                'group' => 'Operation',
                'icon' => 'ti ti-briefcase',
                'sort_order' => 10,
            ],

            [
                'code' => 'asset',
                'name' => 'Asset',
                'group' => 'Operation',
                'icon' => 'ti ti-device-laptop',
                'sort_order' => 11,
            ],

            [
                'code' => 'knowledge',
                'name' => 'Knowledge',
                'group' => 'Operation',
                'icon' => 'ti ti-book',
                'sort_order' => 12,
            ],

            [
                'code' => 'meeting',
                'name' => 'Meeting',
                'group' => 'Operation',
                'icon' => 'ti ti-users-group',
                'sort_order' => 13,
            ],

            [
                'code' => 'report',
                'name' => 'Report',
                'group' => 'Report',
                'icon' => 'ti ti-report',
                'sort_order' => 14,
            ],

            [
                'code' => 'setting',
                'name' => 'Setting',
                'group' => 'System',
                'icon' => 'ti ti-settings',
                'sort_order' => 15,
            ],

        ];

        foreach ($modules as $module) {

            DB::table('modules')->updateOrInsert(

                [
                    'code' => $module['code'],
                ],

                [

                    'name' => $module['name'],

                    'label' => $module['name'],

                    'group' => $module['group'],

                    'icon' => $module['icon'],

                    'sort_order' => $module['sort_order'],

                    'is_active' => true,

                    'is_visible' => true,

                    'is_system' => true,

                    'created_at' => now(),

                    'updated_at' => now(),

                ]

            );

        }
    }
}