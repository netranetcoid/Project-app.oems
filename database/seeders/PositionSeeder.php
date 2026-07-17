<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $company = DB::table('companies')
            ->where('code', 'OEMS')
            ->first();

        if (!$company) {
            return;
        }

        $divisions = DB::table('divisions')
            ->where('company_id', $company->id)
            ->pluck('id', 'code');

        $positions = [

            // ======================
            // MANAGEMENT
            // ======================

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

            // ======================
            // HR
            // ======================

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
            ],

            // ======================
            // FINANCE
            // ======================

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
            ],

            // ======================
            // NOC
            // ======================

            [
                'division' => 'NOC',
                'code' => 'NOC_LEADER',
                'name' => 'Leader NOC',
                'level' => 40,
                'type' => 'leader',
                'is_approver' => true,
            ],

            [
                'division' => 'NOC',
                'code' => 'NOC_STAFF',
                'name' => 'NOC Staff',
                'level' => 20,
                'type' => 'staff',
            ],

            // ======================
            // TEKNISI
            // ======================

            [
                'division' => 'TECH',
                'code' => 'TECH_LEADER',
                'name' => 'Leader Teknisi',
                'level' => 40,
                'type' => 'leader',
                'is_field_worker' => true,
                'is_approver' => true,
            ],

            [
                'division' => 'TECH',
                'code' => 'TECHNICIAN',
                'name' => 'Teknisi',
                'level' => 20,
                'type' => 'staff',
                'is_field_worker' => true,
            ],

        ];

        foreach ($positions as $item) {

            DB::table('positions')->updateOrInsert(

                [
                    'company_id' => $company->id,
                    'code' => $item['code'],
                ],

                [

                    'division_id' => $divisions[$item['division']] ?? null,

                    'name' => $item['name'],

                    'level' => $item['level'],

                    'type' => $item['type'],

                    'is_management' => $item['is_management'] ?? false,

                    'is_approver' => $item['is_approver'] ?? false,

                    'is_field_worker' => $item['is_field_worker'] ?? false,

                    'is_kpi_enabled' => true,

                    'is_payroll_enabled' => true,

                    'sort_order' => $item['level'],

                    'status' => 'active',

                    'created_at' => now(),

                    'updated_at' => now(),

                ]

            );

        }
    }
}