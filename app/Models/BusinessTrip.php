<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class BusinessTrip extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','daily_allowance'=>'decimal:2','transport_budget'=>'decimal:2','lodging_budget'=>'decimal:2','other_budget'=>'decimal:2','advance_amount'=>'decimal:2','actual_amount'=>'decimal:2','settlement_difference'=>'decimal:2','delegation_used'=>'boolean','hr_approved_at'=>'datetime','owner_approved_at'=>'datetime','departed_at'=>'datetime','returned_at'=>'datetime','settled_at'=>'datetime','policy_snapshot'=>'array','settlement_items'=>'array'];
    public function scopeForCompany(Builder $query,int $companyId):Builder{return $query->where('company_id',$companyId);}
    public function employee():BelongsTo{return $this->belongsTo(Employee::class);}
    public function branch():BelongsTo{return $this->belongsTo(Branch::class);}
}
