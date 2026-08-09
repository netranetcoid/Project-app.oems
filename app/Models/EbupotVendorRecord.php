<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EbupotVendorRecord extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $casts = [
        'period' => 'date', 'invoice_date' => 'date', 'due_date' => 'date', 'ebupot_date' => 'date', 'paid_at' => 'datetime',
        'sent_at' => 'datetime', 'checklist' => 'array', 'tax_base' => 'decimal:2', 'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2', 'vat_amount' => 'decimal:2', 'stamp_amount' => 'decimal:2',
        'invoice_total' => 'decimal:2', 'net_transfer' => 'decimal:2', 'requires_escalation' => 'boolean',
    ];

    public function scopeForCompany(Builder $query, int $companyId): Builder { return $query->where('company_id', $companyId); }
    public function vendor(): BelongsTo { return $this->belongsTo(EbupotVendor::class, 'ebupot_vendor_id'); }
}
