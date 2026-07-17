<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class VehicleExpense extends Model
{
    protected $guarded=['id'];
    protected $casts=['planned_amount'=>'decimal:2','actual_amount'=>'decimal:2','planned_payment_date'=>'date','approved_at'=>'datetime'];
    public function scopeForCompany(Builder $query,int $companyId):Builder{return $query->where('company_id',$companyId);}
    public function vehicle():BelongsTo{return $this->belongsTo(OperationalVehicle::class,'operational_vehicle_id');}
}
