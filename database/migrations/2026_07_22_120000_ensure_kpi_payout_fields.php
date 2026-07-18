<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migration historis payout dijalankan sebelum tabel KPI dibuat pada
        // instalasi baru. Guard ini memastikan fresh migrate tetap memiliki
        // field yang diperlukan payroll/bonus dan API mobile.
        if (! Schema::hasTable('employee_kpi_assessments')) {
            return;
        }

        Schema::table('employee_kpi_assessments', function (Blueprint $table): void {
            if (! Schema::hasColumn('employee_kpi_assessments', 'payout_date')) {
                $table->date('payout_date')->nullable()->after('bonus_amount');
            }
            if (! Schema::hasColumn('employee_kpi_assessments', 'source_summary')) {
                $table->json('source_summary')->nullable()->after('review_note');
            }
        });
    }

    public function down(): void
    {
        // Tidak menurunkan field agar rollback tidak menghapus dasar audit KPI.
    }
};
