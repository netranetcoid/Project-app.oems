<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CleaningDutyLog extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['duty_date' => 'date', 'completed_at' => 'datetime'];

    public function scopeForCompany(Builder $query, int $companyId): Builder { return $query->where('company_id', $companyId); }
    public function schedule(): BelongsTo { return $this->belongsTo(CleaningDutySchedule::class, 'cleaning_duty_schedule_id'); }
    public function division(): BelongsTo { return $this->belongsTo(Division::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function task(): BelongsTo { return $this->belongsTo(EmployeeTask::class, 'employee_task_id'); }
    public function items(): HasMany { return $this->hasMany(CleaningDutyLogItem::class); }
}
