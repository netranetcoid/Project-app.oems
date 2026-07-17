<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\IntegrationConnection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AppBillIntegrationSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('integration_connections')) {
            return;
        }

        foreach (Company::query()->active()->get() as $company) {
            // firstOrCreate menjaga tanggal cutover dan limit yang sudah
            // diedit owner agar tidak kembali ke default saat seeder diulang.
            IntegrationConnection::firstOrCreate(
                ['company_id' => $company->id, 'provider' => 'appbill'],
                [
                    'name' => 'AppBill',
                    'mode' => 'mock',
                    'base_url' => null,
                    'auth_type' => 'none',
                    'is_enabled' => true,
                    'allow_inbound' => true,
                    'allow_outbound' => true,
                    'verify_tls' => true,
                    'timeout_seconds' => 15,
                    'retry_limit' => 3,
                    'health_status' => 'ready',
                    'settings' => [
                        'dummy_only' => true,
                        'live_activation_confirmed' => false,
                        'cutover_date_editable' => true,
                        'payload_schema_version' => '1.0',
                    ],
                ]
            );
        }
    }
}
