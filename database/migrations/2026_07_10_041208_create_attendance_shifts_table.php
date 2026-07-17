<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_shifts', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Company & Site
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // NULL = Berlaku untuk semua Site
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            $table->string('code', 50);
            $table->string('name');

            $table->enum('work_type', [
                'office',
                'shift',
                'flexible'
            ])->default('office');

            /*
            |--------------------------------------------------------------------------
            | Working Time
            |--------------------------------------------------------------------------
            */

            $table->time('clock_in_time');

            $table->time('clock_out_time');

            $table->time('break_start')
                ->nullable();

            $table->time('break_end')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Attendance Rules
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('work_hours')
                ->default(8);

            $table->unsignedInteger('grace_in_minutes')
                ->default(15);

            $table->unsignedInteger('grace_out_minutes')
                ->default(0);

            $table->unsignedInteger('late_tolerance_minutes')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Overtime
            |--------------------------------------------------------------------------
            */

            $table->boolean('allow_overtime')
                ->default(true);

            $table->unsignedInteger('overtime_after_minutes')
                ->default(30);

            /*
            |--------------------------------------------------------------------------
            | Attendance Validation
            |--------------------------------------------------------------------------
            */

            $table->boolean('gps_required')
                ->default(true);

            $table->boolean('selfie_required')
                ->default(true);

            $table->boolean('photo_required')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->string('status', 30)
                ->default('active');

            $table->json('settings')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'company_id',
                'branch_id',
                'code'
            ]);

            $table->index([
                'company_id',
                'branch_id',
                'status'
            ]);

            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_shifts');
    }
};