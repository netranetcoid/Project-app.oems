<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default db:seed harus aman untuk VPS baru: hanya master dan akun
        // bootstrap. Demo hanya boleh dipanggil eksplisit di QA.
        $this->call(FirstLoginSeeder::class);
        // Seed hanya membuat fallback lokasi, tidak menimpa policy Developer.
        $this->call(AttendanceLocationPolicySeeder::class);
    }
}
