<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('employee_work_location_tracks', function (Blueprint $table): void { $table->decimal('distance_from_previous_meters', 12, 2)->default(0)->after('accuracy_meters'); }); } public function down(): void { Schema::table('employee_work_location_tracks', fn(Blueprint $table) => $table->dropColumn('distance_from_previous_meters')); } };
