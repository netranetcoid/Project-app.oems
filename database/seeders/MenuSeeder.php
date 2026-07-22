<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Seeder boleh dijalankan berulang pada QA/production tanpa menghapus
        // menu custom milik owner. Hanya menu sistem dengan code yang sama yang
        // diperbarui agar route dan permission selalu mengikuti aplikasi.
        $modules = DB::table('modules')->pluck('id', 'code');

        // Menu bawaan lama mengarah ke route yang tidak pernah terdaftar.
        // Nonaktifkan hanya record sistem; menu custom owner tidak disentuh.
        DB::table('menus')
            ->where('is_system', true)
            ->whereIn('code', [
                'company', 'position', 'leave', 'overtime', 'payroll',
                'users', 'roles', 'permissions', 'menus',
                // Menu berikut pernah memecah satu halaman yang sama menjadi
                // banyak link. Nonaktifkan agar sidebar ringkas; filter tetap
                // tersedia sebagai tab di dalam pusat pengajuan/operasional.
                'master-request-policy', 'hr-leave', 'hr-permission-sick',
                'hr-overtime', 'hr-finance-requests', 'hr-business-trips',
                'hr-vehicles',
                // Semua menu berikut adalah kontrol yang dibaca/dipakai APK
                // OvallHR. Mereka dipusatkan ke satu halaman agar HR tidak
                // perlu mencari di tiga kelompok sidebar yang berbeda.
                'hr-settings', 'master-attendance-shifts',
                'master-shift-assignments', 'master-compensation',
                'master-kpi-aspects', 'attendance', 'hr-requests',
                'hr-payroll', 'hr-kpi', 'mobile-release-center',
                // Roadmap pernah ditambahkan sebagai eksperimen visual. Owner
                // meminta modulnya dicabut; sembunyikan record lama saat seeder
                // dijalankan ulang tanpa menyentuh menu custom lain.
                'employee-roadmap',
                // Parent/child legacy ini dahulu dibuat oleh compatibility
                // seeder dan menjadi sumber menu System ganda.
                'settings', 'settings.role-permission', 'settings.user-access',
            ])
            ->update(['is_active' => false, 'updated_at' => now()]);

        $dashboard = $this->upsertMenu([
            'module_id' => $modules['dashboard'] ?? null,
            'code' => 'dashboard',
            'name' => 'Dashboard',
            'label' => 'Dashboard',
            'icon' => 'ri ri-dashboard-line',
            'route_name' => 'dashboard',
            'permission_name' => 'dashboard.view',
            'sort_order' => 1,
            'level' => 1,
        ]);

        $master = $this->upsertMenu([
            'module_id' => $modules['master'] ?? null,
            'code' => 'master',
            'name' => 'Master Data',
            'label' => 'Master Data',
            'icon' => 'ri ri-database-2-line',
            'sort_order' => 2,
            'level' => 1,
        ]);

        $hr = $this->upsertMenu([
            'module_id' => $modules['employee'] ?? null,
            'code' => 'hr',
            'name' => 'Human Resource',
            'label' => 'Human Resource',
            'icon' => 'ri ri-team-line',
            'sort_order' => 3,
            'level' => 1,
        ]);

        // Ini sengaja berupa satu menu langsung (bukan parent dengan banyak
        // submenu). Seluruh pintasan dan approval OvallHR ada di dalam page
        // Control Center sehingga sidebar tetap singkat di layar laptop/HP.
        $this->upsertMenu([
            'module_id' => $modules['attendance'] ?? null,
            'code' => 'ovallhr-control-center',
            'name' => 'OvallHR Control',
            'label' => 'OvallHR Control',
            'icon' => 'ri ri-smartphone-line',
            'route_name' => 'ovallhr.control-center.index',
            'permission_name' => 'attendance.view',
            'sort_order' => 4,
            'level' => 1,
        ]);

        $future = $this->upsertMenu([
            'module_id' => $modules['project'] ?? null,
            'code' => 'isp-roadmap',
            'name' => 'Pengembangan ISP',
            'label' => 'Pengembangan ISP',
            'icon' => 'ri ri-router-line',
            'badge_text' => 'COMING SOON',
            'badge_color' => 'info',
            'sort_order' => 90,
            'level' => 1,
        ]);

        $system = $this->upsertMenu([
            'module_id' => $modules['setting'] ?? null,
            'code' => 'system',
            'name' => 'System',
            'label' => 'System',
            'icon' => 'ri ri-settings-3-line',
            'sort_order' => 99,
            'level' => 1,
        ]);

        // Master yang sudah memiliki route aktif. Perusahaan PT OSM tetap
        // PT OSM adalah legal entity; struktur operasionalnya Branch -> Site.
        $this->child($modules['setting'] ?? null, $master, 'hr-settings', 'Aturan HR & Absensi', 'hr.settings.index', 'attendance.view', 1, 'ri ri-settings-4-line');
        $this->child($modules['master'] ?? null, $master, 'branch', 'Branch / Site', 'master.branches.index', 'branch.view', 2, 'ri ri-building-2-line');
        $this->child($modules['master'] ?? null, $master, 'division', 'Divisi', 'master.divisions.index', 'division.view', 3, 'ri ri-node-tree');
        $this->child($modules['master'] ?? null, $master, 'position', 'Jabatan', 'master.positions.index', 'position.view', 4, 'ri ri-organization-chart');
        $this->child($modules['master'] ?? null, $master, 'contract-type', 'Jenis Kontrak', 'master.contract-types.index', 'contract-type.view', 5, 'ri ri-file-list-3-line');
        $this->child($modules['master'] ?? null, $master, 'company-documents', 'Master Dokumen', 'master.company-documents.index', 'company-document.view', 6, 'ri ri-folder-paper-line');
        $this->child($modules['attendance'] ?? null, $master, 'master-attendance-shifts', 'Shift Kerja', 'attendance.shifts.index', 'attendance.shift.view', 6, 'ri ri-time-line');
        $this->child($modules['attendance'] ?? null, $master, 'master-shift-assignments', 'Jadwal / Penugasan', 'attendance.shift-assignments.index', 'attendance.shift.assignment.view', 7, 'ri ri-calendar-schedule-line');
        $this->child($modules['payroll'] ?? null, $master, 'master-compensation', 'Gaji & Tunjangan', 'hr.compensation.index', 'payroll.view', 8, 'ri ri-wallet-3-line');
        $this->child($modules['kpi'] ?? null, $master, 'master-kpi-aspects', 'Aspek & Bobot KPI', 'hr.kpi.aspects', 'kpi.view', 9, 'ri ri-bar-chart-grouped-line');

        // Satu kelompok HR menggantikan link lama leave.view/payroll.view yang
        // bukan nama route dan sebelumnya hanya menghasilkan menu buntu.
        $this->child($modules['employee'] ?? null, $hr, 'employee', 'Pegawai', 'employees.index', 'employees.view', 1, 'ri ri-user-settings-line');
        $this->child($modules['employee'] ?? null, $hr, 'employee-contracts', 'Kontrak Kerja', 'hr.contracts.index', 'employees.view', 2, 'ri ri-file-paper-2-line');
        $this->child($modules['attendance'] ?? null, $hr, 'attendance', 'Absensi', 'attendance.index', 'attendance.view', 3, 'ri ri-fingerprint-line');
        $this->child($modules['leave'] ?? null, $hr, 'hr-requests', 'Pengajuan & Approval', 'hr.requests.index', 'hr-request.view', 4, 'ri ri-file-check-line');
        // Menu visible tunggal untuk payroll dan seluruh laporan biaya pegawai.
        $this->child($modules['payroll'] ?? null, $hr, 'employee-cost-center', 'Payroll & Biaya Karyawan', 'hr.employee-costs.index', 'employee-cost.view', 5, 'ri ri-funds-line');
        $this->child($modules['payroll'] ?? null, $hr, 'hr-payroll', 'Payroll & Slip Gaji', 'hr.payroll.index', 'payroll.view', 6, 'ri ri-money-dollar-circle-line');
        $this->child($modules['kpi'] ?? null, $hr, 'hr-kpi', 'KPI & Bonus', 'hr.kpi.index', 'kpi.view', 7, 'ri ri-line-chart-line');
        $this->child($modules['project'] ?? null, $hr, 'hr-operations', 'Dinas & Motor Operasional', 'hr.operations.index', 'business-trip.view', 8, 'ri ri-road-map-line');
        $this->child($modules['employee'] ?? null, $hr, 'bpjs-readiness', 'BPJS & Kepatuhan', 'hr.bpjs-readiness.index', 'bpjs-registration.view', 9, 'ri ri-shield-check-line');

        foreach ([
            ['isp-ticketing', 'Ticket & SLA', 'ri ri-customer-service-2-line'],
            ['isp-network-inventory', 'Inventory Jaringan', 'ri ri-radar-line'],
            ['isp-noc-monitoring', 'NOC & Monitoring', 'ri ri-pulse-line'],
            ['isp-provisioning', 'Provisioning Pelanggan', 'ri ri-base-station-line'],
            ['isp-appbill', 'AppBill Live & Rekonsiliasi', 'ri ri-plug-2-line'],
        ] as $index => [$code, $name, $icon]) {
            $this->upsertMenu([
                'module_id' => $modules['project'] ?? null,
                'parent_id' => $future,
                'code' => $code,
                'name' => $name,
                'label' => $name,
                'icon' => $icon,
                'badge_text' => 'SOON',
                'badge_color' => 'secondary',
                'sort_order' => $index + 1,
                'level' => 2,
            ]);
        }

        $this->child($modules['setting'] ?? null, $system, 'user-access', 'Akses Pengguna', 'settings.user-access.index', 'users.view', 1, 'ri ri-user-settings-line');
        $this->child($modules['setting'] ?? null, $system, 'role-permission', 'Role & Permission', 'settings.role-permission.index', 'roles.view', 2, 'ri ri-shield-keyhole-line');
        $this->child($modules['setting'] ?? null, $system, 'integration-center', 'Integrasi, Audit & Health', 'settings.integrations.index', 'integration.view', 3, 'ri ri-shield-check-line');
        $this->child($modules['payroll'] ?? null, $system, 'bpjs-calculation-engine', 'BPJS Calculation Engine', 'settings.bpjs-calculation.index', 'bpjs-calculation.view', 4, 'ri ri-calculator-line');
        $this->child($modules['setting'] ?? null, $system, 'mobile-release-center', 'Mobile Release Center', 'settings.mobile-releases.index', 'mobile-release.view', 5, 'ri ri-smartphone-line');

        // child() di atas mengaktifkan ulang menu sistem saat upsert. Jalankan
        // penyederhanaan di akhir supaya hanya OvallHR Control yang terlihat
        // pada sidebar; route asal tetap hidup sebagai tujuan kartu di pusat.
        DB::table('menus')
            ->where('is_system', true)
            ->whereIn('code', [
                'hr-settings', 'master-attendance-shifts',
                'master-shift-assignments', 'master-compensation',
                'master-kpi-aspects', 'attendance', 'hr-requests',
                'hr-payroll', 'hr-kpi', 'mobile-release-center',
            ])
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    private function child(?int $moduleId, int $parentId, string $code, string $name, string $route, string $permission, int $sort, string $icon): int
    {
        return $this->upsertMenu([
            'module_id' => $moduleId,
            'parent_id' => $parentId,
            'code' => $code,
            'name' => $name,
            'label' => $name,
            'icon' => $icon,
            'route_name' => $route,
            'permission_name' => $permission,
            'sort_order' => $sort,
            'level' => 2,
        ]);
    }

    private function upsertMenu(array $attributes): int
    {
        $existing = DB::table('menus')->where('code', $attributes['code'])->first();
        $now = now();
        $values = array_merge([
            'parent_id' => null,
            'type' => 'menu',
            'icon' => null,
            'url' => null,
            'route_name' => null,
            'permission_name' => null,
            'target' => '_self',
            'badge_text' => null,
            'badge_color' => null,
            'sort_order' => 0,
            'level' => 1,
            'is_active' => true,
            'is_system' => true,
            'open_in_new_tab' => false,
            'deleted_at' => null,
            'updated_at' => $now,
        ], $attributes);

        if ($existing) {
            // Visibilitas menu existing sengaja tidak diubah agar pilihan owner
            // untuk menyembunyikan menu tetap dihormati saat seeder diulang.
            DB::table('menus')->where('id', $existing->id)->update($values);
            return (int) $existing->id;
        }

        $values['is_visible'] = true;
        $values['created_at'] = $now;
        return (int) DB::table('menus')->insertGetId($values);
    }
}
