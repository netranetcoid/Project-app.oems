<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kpi_aspects')) {
            Schema::create('kpi_aspects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->string('category', 100)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('kpi_standards')) {
            Schema::create('kpi_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->decimal('bonus_maximum', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'position_id', 'name']);
            $table->index(['company_id', 'position_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('kpi_standard_items')) {
            Schema::create('kpi_standard_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_standard_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_aspect_id')->nullable()->constrained()->nullOnDelete();
            $table->string('aspect_name', 150);
            $table->text('guideline')->nullable();
            $table->decimal('weight', 5, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['kpi_standard_id', 'sort_order']);
            });
        }

        if (!Schema::hasTable('employee_kpi_assessments')) {
            Schema::create('employee_kpi_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('kpi_standard_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('submitted');
            $table->decimal('total_score', 6, 2)->default(0);
            $table->char('grade', 1)->nullable();
            $table->decimal('bonus_maximum', 15, 2)->default(0);
            $table->decimal('bonus_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'employee_id', 'period_month', 'period_year'], 'employee_kpi_assessment_period_unique');
            // Nama otomatis Laravel melewati batas identifier MySQL 64
            // karakter; gunakan nama stabil agar migrate lintas server aman.
            $table->index(
                ['company_id', 'status', 'period_year', 'period_month'],
                'ekpi_company_status_period_idx'
            );
            });
        }

        // Jika migrate sebelumnya berhenti setelah membuat tabel tetapi saat
        // menambah index gagal (misalnya batas 64 karakter), lanjutkan secara
        // idempotent dengan memastikan index pendek ini tersedia.
        if (Schema::hasTable('employee_kpi_assessments')) {
            // Schema::hasIndex bekerja di MySQL dan SQLite; query lama ke
            // information_schema membuat seluruh test database SQLite gagal.
            if (! Schema::hasIndex('employee_kpi_assessments', 'ekpi_company_status_period_idx')) {
                Schema::table('employee_kpi_assessments', function (Blueprint $table): void {
                    $table->index(
                        ['company_id', 'status', 'period_year', 'period_month'],
                        'ekpi_company_status_period_idx'
                    );
                });
            }
        }

        if (!Schema::hasTable('employee_kpi_assessment_items')) {
            Schema::create('employee_kpi_assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_kpi_assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_aspect_id')->nullable()->constrained()->nullOnDelete();
            $table->string('aspect_name', 150);
            $table->text('guideline')->nullable();
            $table->decimal('weight', 5, 2);
            $table->decimal('score', 5, 2);
            $table->decimal('weighted_score', 6, 2);
            $table->string('source_type', 30)->default('manual');
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_kpi_assessment_items');
        Schema::dropIfExists('employee_kpi_assessments');
        Schema::dropIfExists('kpi_standard_items');
        Schema::dropIfExists('kpi_standards');
        Schema::dropIfExists('kpi_aspects');
    }
};
