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
            $connection = IntegrationConnection::firstOrCreate(
                ['company_id' => $company->id, 'provider' => 'appbill'],
                [
                    'name' => 'AppBill',
                    'mode' => 'mock',
                    // Base URL resmi AppBill. Mode tetap mock sampai owner
                    // mengisi/merotasi token-HMAC dan menyetujui cutover.
                    'base_url' => 'https://ovallfiber.osm.net.id',
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
                        // Endpoint khusus handshake agar tombol uji langsung
                        // tidak pernah diarahkan ke endpoint payroll/absensi.
                        'connection_test_path' => '/api/v1/integrations/appoems/connection-test',
                    ],
                ]
            );

            // Seeder tidak menimpa URL/token owner yang sudah hidup, tetapi
            // mengisi base URL resmi bila koneksi mock lama masih kosong.
            if (blank($connection->base_url)) {
                $connection->update(['base_url' => 'https://ovallfiber.osm.net.id']);
            }
        }
    }
}
