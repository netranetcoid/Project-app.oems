<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeAttendance extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'date' => 'date', 'clock_in_at' => 'datetime', 'clock_out_at' => 'datetime',
        'client_occurred_at' => 'datetime', 'retention_until' => 'date',
    ];
    public function attendance() { return $this->belongsTo(Attendance::class); }
    public function shift() { return $this->belongsTo(AttendanceShift::class, 'attendance_shift_id'); }
}
