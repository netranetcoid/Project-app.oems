<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('companies')->updateOrInsert(

            [
                'code' => 'OEMS',
            ],

            [

                'name' => 'PT. Ovall Solusindo Mandiri',

                'legal_name' => 'PT. Ovall Solusindo Mandiri',

                'brand_name' => 'OSM',

                'business_type' => 'Internet Service Provider',

                'industry_type' => 'Telecommunication and ISP',

                'email' => 'developer@oems.local',

                'phone' => '021000000',

                'website' => 'https://oems.local',

                'default_currency' => 'IDR',

                'timezone' => 'Asia/Jakarta',

                'attendance_gps_required' => true,

                'attendance_radius_meter' => 150,

                // Koordinat kantor PT OSM untuk fallback geofence server.
                'settings' => json_encode([
                    'office_name' => 'Kantor PT OSM',
                    'office_latitude' => -6.612755088971767,
                    'office_longitude' => 106.75548743646192,
                    'attendance_retention_days' => 60,
                    'attendance_selfie_required' => true,
                ]),

                'salary_calculation_type' => 'monthly',

                'status' => 'active',

                'created_at' => now(),

                'updated_at' => now(),

            ]

        );
    }
}
