<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BusinessTripPolicy extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['daily_allowance'=>'decimal:2','default_monthly_advance'=>'decimal:2','transport_paid_by_company'=>'boolean','owner_approval_required'=>'boolean','hr_delegation_limit'=>'decimal:2','delegation_valid_until'=>'date','settings'=>'array'];
}
