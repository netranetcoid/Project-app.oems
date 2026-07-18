<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master pengumuman untuk APK OvallHR. Tidak menyimpan data sensitif
     * karyawan; hanya teks komunikasi yang ditarik oleh endpoint home.
     */
    public function up(): void
    {
        Schema::create('mobile_announcements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Query beranda APK selalu memfilter company, status, dan masa
            // berlaku. Index ini menjaga response tetap ringan saat data besar.
            $table->index(['company_id', 'is_active', 'published_at'], 'mob_ann_company_active_pub_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_announcements');
    }
};
