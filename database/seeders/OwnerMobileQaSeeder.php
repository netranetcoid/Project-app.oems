<?php

namespace Database\Seeders;

use App\Services\Employee\OwnerMobileQaProvisioner;
use Illuminate\Database\Seeder;

/** Creates Owner QA from env values; password is never committed to source. */
class OwnerMobileQaSeeder extends Seeder
{
    public function run(): void
    {
        $password = (string) env('OEMS_OWNER_QA_PASSWORD', '');
        if (strlen($password) < 12) {
            throw new \RuntimeException('Set OEMS_OWNER_QA_PASSWORD minimal 12 karakter untuk membuat akun Owner QA.');
        }

        $result = app(OwnerMobileQaProvisioner::class)->provision(
            (string) env('OEMS_OWNER_QA_NAME', 'Owner QA'),
            (string) env('OEMS_OWNER_QA_EMAIL', 'owner@oems.local'),
            (string) env('OEMS_OWNER_QA_USERNAME', 'owner'),
            $password,
        );

        $this->command?->info('Owner QA OvallHR siap: ' . $result['user']->username . ' / ' . $result['user']->email);
    }
}
