<?php

namespace Database\Seeders;

use App\Models\AttendanceLocationPolicy;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

/** Seeds safe editable defaults without overwriting a Developer's policy. */
class AttendanceLocationPolicySeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->each(function (Company $company): void {
            $settings = is_array($company->settings) ? $company->settings : [];
            AttendanceLocationPolicy::query()->firstOrCreate(
                ['company_id' => $company->id, 'scope_type' => 'company', 'scope_id' => null],
                [
                    'name' => 'Kantor Utama ' . $company->name,
                    'mode' => 'geofence',
                    'latitude' => $settings['office_latitude'] ?? -6.612755088971767,
                    'longitude' => $settings['office_longitude'] ?? 106.75548743646192,
                    'radius_meter' => $company->attendance_radius_meter ?? 150,
                    'is_active' => true,
                    'notes' => 'Default perusahaan. Ubah dari Pusat Lokasi Presensi bila diperlukan.',
                ]
            );

            Branch::query()->forCompany($company->id)->whereNotNull('latitude')->whereNotNull('longitude')->each(function (Branch $branch): void {
                AttendanceLocationPolicy::query()->firstOrCreate(
                    ['company_id' => $branch->company_id, 'scope_type' => 'branch', 'scope_id' => $branch->id],
                    [
                        'name' => $branch->name,
                        'mode' => 'geofence',
                        'latitude' => $branch->latitude,
                        'longitude' => $branch->longitude,
                        'radius_meter' => $branch->attendance_radius_meter ?? 150,
                        'is_active' => true,
                        'notes' => 'Diambil dari Master Site/Branch. Dapat diedit Developer di Pusat Lokasi Presensi.',
                    ]
                );
            });
        });
    }
}
