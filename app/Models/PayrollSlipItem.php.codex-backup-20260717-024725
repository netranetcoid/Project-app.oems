<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSlipItem extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'decimal:2', 'is_taxable' => 'boolean', 'metadata' => 'array'];

    public function slip(): BelongsTo { return $this->belongsTo(PayrollSlip::class, 'payroll_slip_id'); }
    public function receivable(): BelongsTo { return $this->belongsTo(EmployeeReceivable::class, 'employee_receivable_id'); }
}
