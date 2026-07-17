<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_app_releases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 20)->default('android');
            $table->string('version_name', 40);
            $table->unsignedInteger('version_code');
            $table->unsignedInteger('minimum_version_code')->default(1);
            $table->string('status', 20)->default('draft'); // draft|published|archived
            $table->boolean('is_force_update')->default(false);
            $table->string('download_url', 2048)->nullable();
            $table->text('release_notes')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'platform', 'version_code'], 'mobile_release_company_platform_version_uq');
            $table->index(['company_id', 'platform', 'status'], 'mobile_release_company_platform_status_idx');
        });

        Schema::create('mobile_feature_flags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('key', 80);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'key'], 'mobile_feature_company_key_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_feature_flags');
        Schema::dropIfExists('mobile_app_releases');
    }
};
