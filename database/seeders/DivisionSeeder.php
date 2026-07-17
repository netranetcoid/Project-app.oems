<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $company = DB::table('companies')
            ->where('code', 'OEMS')
            ->first();

        if (!$company) {
            return;
        }

        $divisions = [

            [
                'code' => 'MANAGEMENT',
                'name' => 'Management',
                'type' => 'management',
                'sort_order' => 1,
            ],

            [
                'code' => 'HR',
                'name' => 'Human Resource',
                'type' => 'hr',
                'sort_order' => 2,
            ],

            [
                'code' => 'FINANCE',
                'name' => 'Finance',
                'type' => 'finance',
                'sort_order' => 3,
            ],

            [
                'code' => 'NOC',
                'name' => 'Network Operation Center',
                'type' => 'noc',
                'sort_order' => 4,
            ],

            [
                'code' => 'TECH',
                'name' => 'Teknisi',
                'type' => 'technician',
                'sort_order' => 5,
            ],

            [
                'code' => 'MARKETING',
                'name' => 'Marketing',
                'type' => 'marketing',
                'sort_order' => 6,
            ],

            [
                'code' => 'SALES',
                'name' => 'Sales',
                'type' => 'sales',
                'sort_order' => 7,
            ],

            [
                'code' => 'CS',
                'name' => 'Customer Service',
                'type' => 'customer-service',
                'sort_order' => 8,
            ],

            [
                'code' => 'WAREHOUSE',
                'name' => 'Warehouse',
                'type' => 'warehouse',
                'sort_order' => 9,
            ],

            [
                'code' => 'PROJECT',
                'name' => 'Project',
                'type' => 'project',
                'sort_order' => 10,
            ],

            [
                'code' => 'IT',
                'name' => 'Information Technology',
                'type' => 'it',
                'sort_order' => 11,
            ],

        ];

        foreach ($divisions as $division) {

            DB::table('divisions')->updateOrInsert(

                [
                    'company_id' => $company->id,
                    'code' => $division['code'],
                ],

                [

                    'name' => $division['name'],

                    'type' => $division['type'],

                    'sort_order' => $division['sort_order'],

                    'is_kpi_enabled' => true,

                    'is_payroll_enabled' => true,

                    'is_attendance_required' => true,

                    'status' => 'active',

                    'created_at' => now(),

                    'updated_at' => now(),

                ]

            );

        }
    }
}