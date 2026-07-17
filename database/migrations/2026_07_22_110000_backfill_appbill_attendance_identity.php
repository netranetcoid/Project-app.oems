<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendances') || ! Schema::hasColumn('attendances', 'source_record_id')) {
            return;
        }

        // Rekam lama dibuat sebelum kontrak AppBill ada. Setiap rekam wajib
        // mempunyai identitas permanen agar normalizer tidak salah merge data.
        DB::table('attendances')
            ->where(function ($query): void {
                $query->whereNull('source_record_id')->orWhere('source_record_id', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($attendances): void {
                foreach ($attendances as $attendance) {
                    DB::table('attendances')->where('id', $attendance->id)->update([
                        'source_record_id' => 'ATT-LEGACY-' . $attendance->id,
                        'sync_version' => max(1, (int) ($attendance->sync_version ?? 1)),
                        'approval_status' => $attendance->approval_status ?: 'approved',
                        'sync_updated_at' => $attendance->sync_updated_at ?: $attendance->updated_at ?: now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Identity yang sudah diekspor tidak boleh dihapus pada rollback.
    }
};
