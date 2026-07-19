<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Bootstrap aman untuk database AppOEMS yang baru selesai dimigrasikan.
 * Seeder ini hanya membuat master wajib dan akun pertama; data pegawai,
 * payroll, kontrak transaksi, serta demo tidak pernah dibuat di production.
 */
class FirstLoginSeeder extends Seeder
{
    public function run(): void
    {
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
            CompanyDocumentSeeder::class,
            BpjsSettingSeeder::class,
            HrRequestPolicySeeder::class,
            KpiFrameworkSeeder::class,
            BusinessTripPolicySeeder::class,
            AppBillIntegrationSeeder::class,
        ]);

        $this->command?->newLine();
        $this->command?->info('Bootstrap AppOEMS selesai. Master PT OSM siap dipakai.');
        $this->command?->line('Login awal: developer atau developer@oems.local');
        $this->command?->line('Password awal diambil dari OEMS_BOOTSTRAP_PASSWORD pada .env.');
    }
}
