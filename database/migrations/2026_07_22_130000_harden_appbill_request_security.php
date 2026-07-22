<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nonce disimpan singkat. Unique index memastikan request tersigned
        // tidak dapat diputar ulang, termasuk ketika dua request tiba bersamaan.
        if (! Schema::hasTable('integration_replay_nonces')) {
            Schema::create('integration_replay_nonces', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('integration_connection_id')->constrained('integration_connections')->cascadeOnDelete();
                $table->string('provider', 50);
                $table->string('nonce', 191);
                $table->string('request_id', 100);
                $table->timestamp('request_timestamp');
                $table->timestamp('expires_at');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['company_id', 'provider', 'nonce'], 'integration_replay_nonce_unique');
                $table->index('expires_at', 'integration_replay_nonce_expiry_idx');
            });
        }

        if (Schema::hasTable('integration_outbox')) {
            Schema::table('integration_outbox', function (Blueprint $table): void {
                if (! Schema::hasColumn('integration_outbox', 'last_attempt_at')) $table->timestamp('last_attempt_at')->nullable()->after('attempts');
                if (! Schema::hasColumn('integration_outbox', 'retry_category')) $table->string('retry_category', 40)->nullable()->after('last_error');
                if (! Schema::hasColumn('integration_outbox', 'retry_after_seconds')) $table->unsignedInteger('retry_after_seconds')->nullable()->after('retry_category');
                if (! Schema::hasColumn('integration_outbox', 'last_error_code')) $table->string('last_error_code', 80)->nullable()->after('retry_after_seconds');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_replay_nonces');
        // Kolom outbox dibiarkan pada rollback agar riwayat retry tidak terhapus.
    }
};
