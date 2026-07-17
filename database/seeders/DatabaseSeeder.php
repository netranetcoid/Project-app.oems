<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan ini sengaja mengikuti dependensi database baru.  Company dan
        // struktur organisasi harus ada sebelum role, developer, dan template
        // kontrak dibuat; jangan menambahkan seeder acak di tengah urutan ini.
        $this->call([
            CompanySeeder::class,
            BranchSeeder::class,
            DivisionSeeder::class,
            PositionSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            DeveloperSeeder::class,
            ModuleSeeder::class,
            MenuSeeder::class,
            MenuRolePermissionFixSeeder::class,
            ContractTemplateSeeder::class,
            HrRequestPolicySeeder::class,
            KpiFrameworkSeeder::class,
            BusinessTripPolicySeeder::class,
            AppBillIntegrationSeeder::class,
        ]);
    }
}
