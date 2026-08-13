<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningDutyItem extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean'];
    public function schedule(): BelongsTo { return $this->belongsTo(CleaningDutySchedule::class, 'cleaning_duty_schedule_id'); }
}
