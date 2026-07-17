<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiStandardItem extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['weight' => 'decimal:2'];

    public function standard(): BelongsTo { return $this->belongsTo(KpiStandard::class, 'kpi_standard_id'); }
    public function aspect(): BelongsTo { return $this->belongsTo(KpiAspect::class, 'kpi_aspect_id'); }
}
