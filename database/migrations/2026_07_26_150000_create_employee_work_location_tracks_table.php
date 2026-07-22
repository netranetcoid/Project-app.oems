<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_work_location_tracks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('overtime_attendance_id')->nullable()->constrained('overtime_attendances')->cascadeOnDelete();
            $table->string('work_mode', 20);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->timestamp('captured_at');
            $table->date('retention_until')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'employee_id', 'captured_at'], 'work_track_employee_time_idx');
            $table->index(['attendance_id', 'captured_at'], 'work_track_attendance_time_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('employee_work_location_tracks'); }
};
