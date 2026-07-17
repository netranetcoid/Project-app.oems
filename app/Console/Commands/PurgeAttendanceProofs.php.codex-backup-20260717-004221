<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeAttendanceProofs extends Command
{
    protected $signature = 'attendance:purge-proofs {--dry-run : Tampilkan jumlah tanpa menghapus bukti}';
    protected $description = 'Hapus selfie/GPS presensi yang melewati retention policy, tanpa menghapus rekap absensi.';

    public function handle(): int
    {
        $query = Attendance::query()
            ->whereNotNull('retention_until')
            ->whereDate('retention_until', '<', now()->toDateString());
        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("{$count} record proof melewati retention.");
            return self::SUCCESS;
        }

        $deleted = 0;
        $query->chunkById(100, function ($attendances) use (&$deleted): void {
            foreach ($attendances as $attendance) {
                foreach (['in_photo', 'out_photo'] as $column) {
                    $path = $attendance->{$column};
                    if ($path) {
                        Storage::disk('public')->delete($path);
                    }
                }

                $attendance->forceFill([
                    'in_photo' => null,
                    'out_photo' => null,
                    'in_latitude' => null,
                    'in_longitude' => null,
                    'out_latitude' => null,
                    'out_longitude' => null,
                    'gps_accuracy_meters' => null,
                    'geofence_distance_meters' => null,
                    'device_id' => null,
                ])->saveQuietly();
                $deleted++;
            }
        });

        $this->info("{$deleted} record proof dibersihkan; rekap absensi tetap tersimpan.");
        return self::SUCCESS;
    }
}
