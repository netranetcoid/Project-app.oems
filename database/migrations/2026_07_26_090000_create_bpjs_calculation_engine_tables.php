<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu konfigurasi aktif per badan usaha. Seluruh tarif berada di DB
        // supaya perubahan aturan tidak pernah menuntut perubahan kode payroll.
        if (! Schema::hasTable('bpjs_settings')) {
            Schema::create('bpjs_settings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->decimal('bpjs_kesehatan_perusahaan', 7, 4);
                $table->decimal('bpjs_kesehatan_karyawan', 7, 4);
                $table->decimal('bpjs_kesehatan_wage_cap', 18, 2)->nullable();
                $table->decimal('jht_perusahaan', 7, 4);
                $table->decimal('jht_karyawan', 7, 4);
                $table->decimal('jp_perusahaan', 7, 4);
                $table->decimal('jp_karyawan', 7, 4);
                $table->decimal('batas_upah_jp', 18, 2)->nullable();
                $table->decimal('jkm', 7, 4);
                $table->decimal('jkk_sangat_rendah', 7, 4);
                $table->decimal('jkk_rendah', 7, 4);
                $table->decimal('jkk_sedang', 7, 4);
                $table->decimal('jkk_tinggi', 7, 4);
                $table->decimal('jkk_sangat_tinggi', 7, 4);
                $table->string('default_jkk_risk_code', 30)->default('rendah');
                $table->boolean('aktif')->default(true);
                $table->date('effective_from')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique('company_id', 'bpjs_settings_company_uq');
            });
        }

        // Risiko JKK dapat disetel per pegawai; jika kosong, payroll memakai
        // risiko default dari konfigurasi perusahaan.
        if (Schema::hasTable('employees') && ! Schema::hasColumn('employees', 'bpjs_jkk_risk_code')) {
            Schema::table('employees', function (Blueprint $table): void {
                $table->string('bpjs_jkk_risk_code', 30)->nullable();
                $table->index(['company_id', 'bpjs_jkk_risk_code'], 'employees_bpjs_jkk_risk_idx');
            });
        }

        if (Schema::hasTable('payroll_periods') && ! Schema::hasColumn('payroll_periods', 'total_company_burden')) {
            Schema::table('payroll_periods', function (Blueprint $table): void {
                $table->decimal('total_company_burden', 18, 2)->default(0);
            });
        }

        if (Schema::hasTable('payroll_slips') && ! Schema::hasColumn('payroll_slips', 'bpjs_wage_base')) {
            Schema::table('payroll_slips', function (Blueprint $table): void {
                $table->decimal('bpjs_wage_base', 18, 2)->default(0);
                $table->decimal('bpjs_kesehatan_perusahaan', 18, 2)->default(0);
                $table->decimal('bpjs_kesehatan_karyawan', 18, 2)->default(0);
                $table->decimal('jht_perusahaan', 18, 2)->default(0);
                $table->decimal('jht_karyawan', 18, 2)->default(0);
                $table->decimal('jp_perusahaan', 18, 2)->default(0);
                $table->decimal('jp_karyawan', 18, 2)->default(0);
                $table->decimal('jkk', 18, 2)->default(0);
                $table->decimal('jkm', 18, 2)->default(0);
                $table->decimal('total_company_burden', 18, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payroll_slips') && Schema::hasColumn('payroll_slips', 'bpjs_wage_base')) {
            Schema::table('payroll_slips', function (Blueprint $table): void {
                $table->dropColumn([
                    'bpjs_wage_base', 'bpjs_kesehatan_perusahaan', 'bpjs_kesehatan_karyawan',
                    'jht_perusahaan', 'jht_karyawan', 'jp_perusahaan', 'jp_karyawan',
                    'jkk', 'jkm', 'total_company_burden',
                ]);
            });
        }

        if (Schema::hasTable('payroll_periods') && Schema::hasColumn('payroll_periods', 'total_company_burden')) {
            Schema::table('payroll_periods', fn (Blueprint $table) => $table->dropColumn('total_company_burden'));
        }

        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'bpjs_jkk_risk_code')) {
            Schema::table('employees', function (Blueprint $table): void {
                $table->dropIndex('employees_bpjs_jkk_risk_idx');
                $table->dropColumn('bpjs_jkk_risk_code');
            });
        }

        Schema::dropIfExists('bpjs_settings');
    }
};
