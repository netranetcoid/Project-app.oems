<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $company = DB::table('companies')
            ->where('code', 'OEMS')
            ->first();

        if (!$company) {
            return;
        }

        DB::table('branches')->updateOrInsert(

            [
                'company_id' => $company->id,
                'code' => 'HO',
            ],

            [

                'name' => 'Head Office',

                'type' => 'head_office',

                'email' => 'developer@oems.local',

                'phone' => '021000000',

                'timezone' => 'Asia/Jakarta',

                'attendance_radius_meter' => 150,

                // Head Office memakai titik kantor PT OSM yang disepakati.
                'latitude' => -6.612755088971767,
                'longitude' => 106.75548743646192,

                'status' => 'active',

                'created_at' => now(),

                'updated_at' => now(),

            ]

        );
    }
}
