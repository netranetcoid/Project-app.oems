<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |------------------------------------------------------------------
        | Master dokumen milik perusahaan
        |------------------------------------------------------------------
        | Ini menyimpan template, bukan transaksi surat. Template dapat
        | diperbarui/naik versi tanpa mengubah kontrak pegawai yang sudah
        | terbit dan tersimpan sebagai snapshot di modul Kontrak.
        */
        Schema::create('company_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 80);
            $table->string('category', 50);
            $table->string('name');
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->longText('body');
            $table->json('settings')->nullable();
            $table->unsignedInteger('template_version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'category', 'is_active']);
        });

        /*
        |------------------------------------------------------------------
        | Kesiapan administrasi BPJS
        |------------------------------------------------------------------
        | Bukan pengganti portal maupun formulir BPJS. Tabel ini menyimpan
        | data internal yang dibutuhkan HR untuk memeriksa dan menyiapkan
        | F1, F1a, serta F2 secara lengkap sebelum dikirim melalui kanal
        | resmi BPJS.
        */
        Schema::create('bpjs_readiness_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('registration_status', 30)->default('draft');
            $table->string('bpjs_ketenagakerjaan_npp', 100)->nullable();
            $table->string('bpjs_kesehatan_registration_no', 100)->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_position')->nullable();
            $table->string('pic_email')->nullable();
            $table->string('pic_phone', 50)->nullable();
            $table->date('target_registration_date')->nullable();
            $table->date('submitted_at')->nullable();
            $table->date('activated_at')->nullable();
            $table->json('document_checklist')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table): void {
            // Field tambahan untuk tracking onboarding. Nomor kepesertaan
            // sudah tersedia di tabel employees inti; kolom ini menjelaskan
            // progres sebelum/ketika nomor tersebut diterbitkan BPJS.
            $table->string('bpjs_registration_status', 30)->default('pending')->after('is_bpjs_ketenagakerjaan_active');
            $table->date('bpjs_effective_date')->nullable()->after('bpjs_registration_status');
            $table->text('bpjs_notes')->nullable()->after('bpjs_effective_date');
            $table->index(['company_id', 'bpjs_registration_status'], 'employees_bpjs_registration_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropIndex('employees_bpjs_registration_status_index');
            $table->dropColumn(['bpjs_registration_status', 'bpjs_effective_date', 'bpjs_notes']);
        });

        Schema::dropIfExists('bpjs_readiness_profiles');
        Schema::dropIfExists('company_documents');
    }
};
