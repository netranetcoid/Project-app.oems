<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_work_location_tracks', function (Blueprint $table): void {
            // Snapshot akun/token yang mengirim titik. Email tetap tersimpan untuk
            // audit bila profil pegawai berubah di masa depan.
            $table->foreignId('user_id')->nullable()->after('employee_id')
                ->constrained('users')->nullOnDelete();
            $table->string('account_email', 190)->nullable()->after('user_id');
            $table->string('installation_id', 120)->nullable()->after('account_email');

            // Bukan satu-satunya anti-kecurangan: status ini memisahkan titik
            // normal, titik yang perlu review HR, dan mock GPS yang diblokir dari km.
            $table->boolean('is_mock_location')->default(false)->after('accuracy_meters');
            $table->string('integrity_status', 20)->default('accepted')->after('is_mock_location');
            $table->unsignedTinyInteger('risk_score')->default(0)->after('integrity_status');
            $table->json('risk_flags')->nullable()->after('risk_score');

            $table->index(['company_id', 'user_id', 'captured_at'], 'work_track_user_time_idx');
            $table->index(['company_id', 'integrity_status', 'captured_at'], 'work_track_integrity_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('employee_work_location_tracks', function (Blueprint $table): void {
            $table->dropIndex('work_track_user_time_idx');
            $table->dropIndex('work_track_integrity_time_idx');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'account_email', 'installation_id', 'is_mock_location',
                'integrity_status', 'risk_score', 'risk_flags',
            ]);
        });
    }
};
