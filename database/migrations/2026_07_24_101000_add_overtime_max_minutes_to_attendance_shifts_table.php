<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_shifts', function (Blueprint $table): void {
            $table->unsignedInteger('overtime_max_minutes')->default(180)->after('overtime_after_minutes');
        });
    }
    public function down(): void
    {
        Schema::table('attendance_shifts', function (Blueprint $table): void {
            $table->dropColumn('overtime_max_minutes');
        });
    }
};
