<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CleaningDutySchedule extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date', 'is_active' => 'boolean'];

    public function scopeForCompany(Builder $query, int $companyId): Builder { return $query->where('company_id', $companyId); }
    public function division(): BelongsTo { return $this->belongsTo(Division::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function logs(): HasMany { return $this->hasMany(CleaningDutyLog::class); }
    public function items(): HasMany { return $this->hasMany(CleaningDutyItem::class)->orderBy('sort_order'); }
}
