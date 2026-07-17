<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('attendance_shift_id')->nullable(); // Nyambung ke tabel shifts lu
            $table->date('date');
            
            // Waktu & Bukti Absen
            $table->timestamp('clock_in_at')->nullable();
            $table->timestamp('clock_out_at')->nullable();
            
            // Lokasi GPS & Foto (Penting buat validasi)
            $table->string('in_latitude')->nullable();
            $table->string('in_longitude')->nullable();
            $table->string('in_photo')->nullable(); // Simpan nama file foto selfie
            
            $table->string('out_latitude')->nullable();
            $table->string('out_longitude')->nullable();
            $table->string('out_photo')->nullable();
            
            // Status Absen
            $table->string('status')->default('present'); // present, late, absent, sick
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};