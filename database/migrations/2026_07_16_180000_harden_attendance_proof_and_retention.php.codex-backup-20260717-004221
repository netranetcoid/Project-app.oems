<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attendances')) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table): void {
            // Bukti audit: disimpan sampai retention_until lalu dipurge oleh
            // command terjadwal; data rekap absensi tetap dipertahankan.
            if (!Schema::hasColumn('attendances', 'gps_accuracy_meters')) {
                $table->decimal('gps_accuracy_meters', 8, 2)->nullable()->after('in_longitude');
            }
            if (!Schema::hasColumn('attendances', 'geofence_distance_meters')) {
                $table->decimal('geofence_distance_meters', 10, 2)->nullable()->after('gps_accuracy_meters');
            }
            if (!Schema::hasColumn('attendances', 'geofence_validated')) {
                $table->boolean('geofence_validated')->default(false)->after('geofence_distance_meters');
            }
            if (!Schema::hasColumn('attendances', 'device_id')) {
                $table->string('device_id', 120)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('attendances', 'client_occurred_at')) {
                $table->timestamp('client_occurred_at')->nullable()->after('device_id');
            }
            if (!Schema::hasColumn('attendances', 'retention_until')) {
                $table->date('retention_until')->nullable()->after('client_occurred_at');
            }
            if (!Schema::hasColumn('attendances', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('retention_until');
            }
        });

        // Explicit short index avoids MySQL identifier limits.
        $indexExists = collect(Schema::getIndexes('attendances'))
            ->contains(fn (array $index): bool => ($index['name'] ?? '') === 'att_company_date_idx');
        if (!$indexExists) {
            Schema::table('attendances', function (Blueprint $table): void {
                $table->index(['company_id', 'date'], 'att_company_date_idx');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('attendances')) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table): void {
            $columns = [
                'gps_accuracy_meters',
                'geofence_distance_meters',
                'geofence_validated',
                'device_id',
                'client_occurred_at',
                'retention_until',
                'rejection_reason',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
