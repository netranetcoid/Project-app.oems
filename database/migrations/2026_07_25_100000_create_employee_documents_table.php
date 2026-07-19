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
        | Arsip privat dokumen pegawai
        |------------------------------------------------------------------
        | File disimpan pada disk local/private, bukan public/storage.
        | Akses file hanya lewat controller berizin agar KTP, KK, NPWP,
        | kartu BPJS, dan data lain tidak dapat dibuka melalui URL umum.
        */
        Schema::create('employee_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('title');
            $table->string('original_name');
            $table->string('disk', 40)->default('local');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->char('sha256', 64)->nullable();
            $table->string('status', 30)->default('uploaded');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_sensitive')->default(true);
            $table->date('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'employee_id', 'document_type']);
            $table->index(['company_id', 'status']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
