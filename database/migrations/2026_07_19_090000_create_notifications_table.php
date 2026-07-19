<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kotak masuk notifikasi internal AppOEMS.
 *
 * Tabel standar Laravel ini sengaja dipisahkan dari audit log. Audit log
 * adalah bukti permanen, sedangkan notifikasi adalah pekerjaan yang perlu
 * dilihat/dibaca user (contoh: pengajuan baru dari OvallHR).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notifications')) {
            return;
        }

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
