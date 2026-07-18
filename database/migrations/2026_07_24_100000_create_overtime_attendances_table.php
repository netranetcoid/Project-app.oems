<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Bukti lembur dipisah dari presensi reguler agar payroll/KPI dapat audit. */
    public function up(): void
    {
        Schema::create('overtime_attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('attendance_shift_id')->nullable()->constrained('attendance_shifts')->nullOnDelete();
            $table->date('date');
            $table->timestamp('clock_in_at')->nullable();
            $table->timestamp('clock_out_at')->nullable();
            $table->decimal('in_latitude', 10, 7)->nullable();
            $table->decimal('in_longitude', 10, 7)->nullable();
            $table->decimal('out_latitude', 10, 7)->nullable();
            $table->decimal('out_longitude', 10, 7)->nullable();
            $table->decimal('gps_accuracy_meters', 8, 2)->nullable();
            $table->decimal('geofence_distance_meters', 10, 2)->nullable();
            $table->string('in_photo')->nullable();
            $table->string('out_photo')->nullable();
            $table->timestamp('client_occurred_at')->nullable();
            $table->date('retention_until')->nullable();
            $table->string('device_id', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'employee_id', 'date'], 'ot_company_employee_date_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('overtime_attendances'); }
};
