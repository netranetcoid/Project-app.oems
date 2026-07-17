<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Attendance extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'client_occurred_at' => 'datetime',
        'retention_until' => 'date',
        'gps_accuracy_meters' => 'decimal:2',
        'geofence_distance_meters' => 'decimal:2',
        'geofence_validated' => 'boolean',
        'sync_updated_at' => 'datetime',
        'is_cancelled' => 'boolean',
    ];

    // Ini kunci biar Laravel mau nyimpen data ke kolom-kolom ini
    protected $fillable = [
        'company_id',
        'employee_id',
        'attendance_shift_id',
        'date',
        'clock_in_at',
        'clock_out_at',
        'in_latitude',
        'in_longitude',
        'in_photo',
        'out_latitude',
        'out_longitude',
        'out_photo',
        'status',
        'notes',
        'gps_accuracy_meters',
        'geofence_distance_meters',
        'geofence_validated',
        'device_id',
        'client_occurred_at',
        'retention_until',
        'rejection_reason',
        'source_record_id',
        'sync_version',
        'approval_status',
        'sync_status',
        'sync_updated_at',
        'change_reason',
        'is_cancelled',
    ];

    protected static function booted(): void
    {
        static::creating(function (Attendance $attendance): void {
            // Identifier tidak berubah dan versi awal ini memenuhi kontrak
            // AppBill tanpa mengirim bukti GPS/selfie keluar dari AppOEMS.
            if (! Schema::hasColumn('attendances', 'source_record_id')) {
                return;
            }

            $attendance->source_record_id ??= 'ATT-' . Str::upper((string) Str::uuid());
            $attendance->sync_version ??= 1;
            $attendance->approval_status ??= 'approved';
            $attendance->sync_status ??= 'pending';
            $attendance->sync_updated_at ??= now();
        });
    }

    /**
     * Relasi ke Employee (Karyawan)
     * Jadi lu bisa panggil $attendance->employee->name buat tau siapa yang absen
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relasi ke Shift
     */
    public function shift()
    {
        return $this->belongsTo(AttendanceShift::class, 'attendance_shift_id');
    }
}
