<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_shift_assignments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('attendance_shift_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('start_date');

            $table->date('end_date')->nullable();

            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->softDeletes();

            // MySQL limits identifier names to 64 characters; use a stable
            // short name instead of Laravel's long generated composite name.
            $table->index([
                'company_id',
                'employee_id',
                'start_date'
            ], 'asa_company_employee_start_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_shift_assignments');
    }
};
