<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningDutyLogItem extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['is_completed' => 'boolean'];
    public function log(): BelongsTo { return $this->belongsTo(CleaningDutyLog::class, 'cleaning_duty_log_id'); }
    public function item(): BelongsTo { return $this->belongsTo(CleaningDutyItem::class, 'cleaning_duty_item_id'); }
}
