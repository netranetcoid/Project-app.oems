<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWorkLocationTrack extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'captured_at' => 'datetime',
        'retention_until' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'accuracy_meters' => 'decimal:2',
        'distance_from_previous_meters' => 'decimal:2',
        'is_mock_location' => 'boolean',
        'risk_flags' => 'array',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function attendance(): BelongsTo { return $this->belongsTo(Attendance::class); }
    public function overtimeAttendance(): BelongsTo { return $this->belongsTo(OvertimeAttendance::class); }
}
