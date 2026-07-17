<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('integration_connections')) {
            Schema::create('integration_connections', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('provider', 50);
                $table->string('name');
                $table->string('mode', 20)->default('mock');
                $table->string('base_url')->nullable();
                $table->string('auth_type', 30)->default('none');
                // Credential disimpan sebagai encrypted cast, bukan JSON polos.
                $table->longText('credentials')->nullable();
                $table->boolean('is_enabled')->default(false);
                $table->boolean('allow_inbound')->default(false);
                $table->boolean('allow_outbound')->default(true);
                $table->boolean('verify_tls')->default(true);
                $table->unsignedTinyInteger('timeout_seconds')->default(15);
                $table->unsignedTinyInteger('retry_limit')->default(3);
                $table->timestamp('cutover_at')->nullable();
                $table->string('health_status', 30)->default('not_configured');
                $table->timestamp('last_success_at')->nullable();
                $table->timestamp('last_failure_at')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'provider'], 'integration_company_provider_uq');
            });
        }

        if (! Schema::hasTable('integration_outbox')) {
            Schema::create('integration_outbox', function (Blueprint $table): void {
                $table->id();
                $table->uuid('event_id')->unique();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('integration_connection_id')->constrained('integration_connections')->cascadeOnDelete();
                $table->string('event_type', 100);
                $table->string('aggregate_type', 100)->nullable();
                $table->string('aggregate_id', 100)->nullable();
                $table->string('idempotency_key', 191)->unique();
                // Payload payroll dapat sensitif, sehingga wajib terenkripsi.
                $table->longText('payload');
                $table->string('status', 30)->default('pending');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamp('next_retry_at')->nullable();
                $table->timestamp('locked_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->unsignedSmallInteger('response_status')->nullable();
                $table->json('response_summary')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();
                $table->index(['company_id', 'status', 'next_retry_at'], 'outbox_company_status_retry_idx');
                $table->index(['event_type', 'created_at'], 'outbox_event_created_idx');
            });
        }

        if (! Schema::hasTable('integration_inbox')) {
            Schema::create('integration_inbox', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('integration_connection_id')->constrained('integration_connections')->cascadeOnDelete();
                $table->string('provider', 50);
                $table->string('external_event_id', 191);
                $table->string('idempotency_key', 191);
                $table->char('payload_hash', 64);
                $table->string('status', 30)->default('received');
                $table->timestamp('received_at');
                $table->timestamp('processed_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'provider', 'external_event_id'], 'inbox_company_provider_event_uq');
                $table->unique(['company_id', 'idempotency_key'], 'inbox_company_idempotency_uq');
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->uuid('request_id');
                $table->string('action', 150);
                $table->string('route_name')->nullable();
                $table->string('method', 10);
                $table->string('path', 500);
                $table->unsignedSmallInteger('response_status')->nullable();
                $table->string('subject_type')->nullable();
                $table->string('subject_id', 100)->nullable();
                // Hanya nama field yang berubah, bukan isi data sensitif.
                $table->json('changed_fields')->nullable();
                $table->json('metadata')->nullable();
                $table->char('ip_hash', 64)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamp('occurred_at');
                $table->index(['company_id', 'occurred_at'], 'audit_company_time_idx');
                $table->index(['user_id', 'occurred_at'], 'audit_user_time_idx');
                $table->index('request_id', 'audit_request_idx');
            });
        }

        if (! Schema::hasTable('system_health_snapshots')) {
            Schema::create('system_health_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
                $table->string('component', 80);
                $table->string('status', 20);
                $table->string('message', 500);
                $table->json('metrics')->nullable();
                $table->timestamp('checked_at');
                $table->index(['company_id', 'checked_at'], 'health_company_time_idx');
                $table->index(['component', 'checked_at'], 'health_component_time_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_health_snapshots');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('integration_inbox');
        Schema::dropIfExists('integration_outbox');
        Schema::dropIfExists('integration_connections');
    }
};

