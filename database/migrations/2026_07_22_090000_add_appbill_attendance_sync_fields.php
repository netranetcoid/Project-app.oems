<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table): void {
            // Kolom ini adalah kontrak kanonik AppOEMS <-> AppBill. Bukti
            // GPS/selfie tidak pernah ikut payload sinkronisasi.
            if (! Schema::hasColumn('attendances', 'source_record_id')) {
                $table->string('source_record_id', 120)->nullable()->after('id');
            }
            if (! Schema::hasColumn('attendances', 'sync_version')) {
                $table->unsignedInteger('sync_version')->default(1)->after('status');
            }
            if (! Schema::hasColumn('attendances', 'approval_status')) {
                $table->string('approval_status', 20)->default('approved')->after('sync_version');
            }
            if (! Schema::hasColumn('attendances', 'sync_status')) {
                $table->string('sync_status', 30)->default('pending')->after('approval_status');
            }
            if (! Schema::hasColumn('attendances', 'sync_updated_at')) {
                $table->timestamp('sync_updated_at')->nullable()->after('sync_status');
            }
            if (! Schema::hasColumn('attendances', 'change_reason')) {
                $table->text('change_reason')->nullable()->after('rejection_reason');
            }
            if (! Schema::hasColumn('attendances', 'is_cancelled')) {
                $table->boolean('is_cancelled')->default(false)->after('change_reason');
            }
        });

        $indexes = collect(Schema::getIndexes('attendances'))->pluck('name')->all();
        if (! in_array('att_source_record_uq', $indexes, true)) {
            Schema::table('attendances', function (Blueprint $table): void {
                $table->unique('source_record_id', 'att_source_record_uq');
            });
        }
        if (! in_array('att_company_sync_idx', $indexes, true)) {
            Schema::table('attendances', function (Blueprint $table): void {
                $table->index(['company_id', 'sync_status', 'date'], 'att_company_sync_idx');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table): void {
            $indexes = collect(Schema::getIndexes('attendances'))->pluck('name')->all();
            if (in_array('att_source_record_uq', $indexes, true)) {
                $table->dropUnique('att_source_record_uq');
            }
            if (in_array('att_company_sync_idx', $indexes, true)) {
                $table->dropIndex('att_company_sync_idx');
            }
            foreach (['source_record_id', 'sync_version', 'approval_status', 'sync_status', 'sync_updated_at', 'change_reason', 'is_cancelled'] as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
