<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EbupotVendorSetting extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['payment_deadline_day' => 'integer', 'report_deadline_day' => 'integer'];
    public function scopeForCompany(Builder $query, int $companyId): Builder { return $query->where('company_id', $companyId); }
}
