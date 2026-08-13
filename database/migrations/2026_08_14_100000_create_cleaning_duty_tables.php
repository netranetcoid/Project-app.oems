<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaning_duty_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('duty_type', ['daily_area', 'server_room'])->default('daily_area');
            $table->enum('recurrence', ['weekly', 'monthly'])->default('weekly');
            $table->unsignedTinyInteger('weekday')->nullable(); // ISO 1..7
            $table->unsignedTinyInteger('day_of_month')->nullable();
            $table->string('title', 160);
            $table->text('instructions')->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['company_id', 'duty_type', 'is_active'], 'duty_schedule_company_type_idx');
        });

        Schema::create('cleaning_duty_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cleaning_duty_schedule_id')->constrained()->cascadeOnDelete();
            $table->string('item_name', 180);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('weight')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cleaning_duty_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cleaning_duty_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_task_id')->nullable()->constrained('employee_tasks')->nullOnDelete();
            $table->date('duty_date');
            $table->enum('status', ['pending', 'in_progress', 'submitted', 'completed', 'missed'])->default('pending');
            $table->text('completion_note')->nullable();
            $table->string('evidence_path')->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['cleaning_duty_schedule_id', 'duty_date'], 'duty_log_schedule_date_unique');
            $table->index(['company_id', 'duty_date', 'status'], 'duty_log_company_date_status_idx');
        });

        Schema::create('cleaning_duty_log_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cleaning_duty_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cleaning_duty_item_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['cleaning_duty_log_id', 'cleaning_duty_item_id'], 'duty_log_item_unique');
        });

        Schema::table('mobile_announcements', function (Blueprint $table): void {
            $table->foreignId('division_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->after('division_id')->constrained()->nullOnDelete();
            $table->string('source_type', 50)->nullable()->after('expires_at');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->unique(['company_id', 'source_type', 'source_id'], 'mobile_announcement_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_announcements', function (Blueprint $table): void {
            $table->dropUnique('mobile_announcement_source_unique');
            $table->dropConstrainedForeignId('employee_id');
            $table->dropConstrainedForeignId('division_id');
            $table->dropColumn(['source_type', 'source_id']);
        });
        Schema::dropIfExists('cleaning_duty_log_items');
        Schema::dropIfExists('cleaning_duty_logs');
        Schema::dropIfExists('cleaning_duty_items');
        Schema::dropIfExists('cleaning_duty_schedules');
    }
};
