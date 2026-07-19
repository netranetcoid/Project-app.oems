<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One editable source of truth for attendance location rules. A policy
     * can belong to the company default, a branch/site, or a whole division.
     */
    public function up(): void
    {
        Schema::create('attendance_location_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('scope_type', 20); // company, branch, division
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('name', 120);
            // Geofence validates a fixed office point. Anywhere still retains
            // selfie/GPS evidence, but does not compare it to an office point.
            $table->string('mode', 20)->default('geofence');
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('radius_meter')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'scope_type', 'scope_id'], 'attendance_location_scope_unique');
            $table->index(['company_id', 'is_active']);
        });

        // Overtime shares the same geofence policy and must retain whether a
        // point was validated or the employee belonged to an anywhere scope.
        if (Schema::hasTable('overtime_attendances') && ! Schema::hasColumn('overtime_attendances', 'geofence_validated')) {
            Schema::table('overtime_attendances', function (Blueprint $table): void {
                $table->boolean('geofence_validated')->default(false)->after('geofence_distance_meters');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('overtime_attendances') && Schema::hasColumn('overtime_attendances', 'geofence_validated')) {
            Schema::table('overtime_attendances', function (Blueprint $table): void {
                $table->dropColumn('geofence_validated');
            });
        }
        Schema::dropIfExists('attendance_location_policies');
    }
};
