<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class OperationalVehicle extends Model
{
    use SoftDeletes;
    protected $guarded=['id'];
    protected $casts=['monthly_operational_allowance'=>'decimal:2','monthly_fuel_budget'=>'decimal:2','last_service_date'=>'date','next_service_date'=>'date','is_active'=>'boolean','settings'=>'array'];
    public function scopeForCompany(Builder $query,int $companyId):Builder{return $query->where('company_id',$companyId);}
    public function employee():BelongsTo{return $this->belongsTo(Employee::class);}
    public function branch():BelongsTo{return $this->belongsTo(Branch::class);}
}
