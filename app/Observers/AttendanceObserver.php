<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Services\Integration\AppBillAttendanceService;
use Illuminate\Support\Facades\Schema;

class AttendanceObserver
{
    public function __construct(private AppBillAttendanceService $appBill)
    {
    }

    public function created(Attendance $attendance): void
    {
        if (Schema::hasTable('integration_outbox')) {
            $this->appBill->queueAttendance($attendance, 'attendance.created');
        }
    }

    public function updated(Attendance $attendance): void
    {
        if (! Schema::hasTable('integration_outbox') || $attendance->wasChanged('sync_version')) {
            return;
        }

        $syncedFields = [
            'date', 'clock_in_at', 'clock_out_at', 'attendance_shift_id', 'status',
            'approval_status', 'notes', 'rejection_reason', 'change_reason', 'is_cancelled',
        ];
        if (! $attendance->wasChanged($syncedFields)) {
            return;
        }

        // saveQuietly mencegah observer membuat loop saat menaikkan versi.
        $attendance->forceFill([
            'sync_version' => max(1, (int) $attendance->sync_version + 1),
            'sync_status' => 'pending',
            'sync_updated_at' => now(),
        ])->saveQuietly();

        $this->appBill->queueAttendance($attendance->fresh(['employee', 'shift']), 'attendance.updated');
    }
}
