<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MenuRolePermissionFixSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        /*
        |------------------------------------------------------------------
        | Legacy compatibility cleanup
        |------------------------------------------------------------------
        | Seeder lama membuat parent "Settings" beserta dua child baru,
        | padahal MenuSeeder sudah memiliki parent "System". Akibatnya menu
        | Akses Pengguna dan Role Permission terlihat dua kali. Data legacy
        | tidak dihapus agar histori tetap ada; hanya dinonaktifkan.
        */
        DB::table('menus')
            ->whereIn('code', [
                'settings',
                'settings.role-permission',
                'settings.user-access',
            ])
            ->update([
                'is_active' => false,
                'is_visible' => false,
                'updated_at' => now(),
            ]);

        $this->command?->info('Menu Settings legacy dinonaktifkan; menu System canonical tetap digunakan.');
    }
}

