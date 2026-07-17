<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_contracts', function (Blueprint $table): void {
            $table->date('probation_end_date')->nullable()->after('end_date');
        });
    }

    public function down(): void
    {
        Schema::table('employee_contracts', function (Blueprint $table): void {
            $table->dropColumn('probation_end_date');
        });
    }
};
