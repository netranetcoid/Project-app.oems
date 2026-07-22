<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_inbox', function (Blueprint $table): void {
            $table->string('source_record_id', 120)->nullable()->after('payload_hash');
            $table->unsignedInteger('source_version')->nullable()->after('source_record_id');
            $table->string('event_type', 100)->nullable()->after('source_version');
            $table->unique(
                ['company_id', 'provider', 'source_record_id', 'source_version'],
                'inbox_appbill_source_revision_uq'
            );
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->string('source_system', 50)->nullable()->after('sync_status');
            $table->uuid('external_event_id')->nullable()->after('source_system');
            $table->unsignedInteger('external_revision')->nullable()->after('external_event_id');
            $table->char('external_payload_hash', 64)->nullable()->after('external_revision');
            $table->string('external_changed_by', 191)->nullable()->after('external_payload_hash');
            $table->timestamp('external_changed_at')->nullable()->after('external_changed_by');
            $table->index(['company_id', 'source_system', 'external_event_id'], 'attendance_external_event_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropIndex('attendance_external_event_idx');
            $table->dropColumn([
                'source_system', 'external_event_id', 'external_revision',
                'external_payload_hash', 'external_changed_by', 'external_changed_at',
            ]);
        });

        Schema::table('integration_inbox', function (Blueprint $table): void {
            $table->dropUnique('inbox_appbill_source_revision_uq');
            $table->dropColumn(['source_record_id', 'source_version', 'event_type']);
        });
    }
};
