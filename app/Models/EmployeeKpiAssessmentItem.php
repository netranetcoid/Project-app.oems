<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeKpiAssessmentItem extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['weight' => 'decimal:2', 'score' => 'decimal:2', 'weighted_score' => 'decimal:2'];

    public function assessment(): BelongsTo { return $this->belongsTo(EmployeeKpiAssessment::class, 'employee_kpi_assessment_id'); }
    public function aspect(): BelongsTo { return $this->belongsTo(KpiAspect::class, 'kpi_aspect_id'); }
}
