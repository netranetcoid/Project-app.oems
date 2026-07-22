<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'parent_branch_id',
        'code',
        'name',
        'type',
        'email',
        'phone',
        'mobile_phone',
        'address',
        'province',
        'city',
        'district',
        'village',
        'postal_code',
        'latitude',
        'longitude',
        'attendance_radius_meter',
        'timezone',
        'opened_at',
        'closed_at',
        'pic_user_id',
        'pic_name',
        'pic_phone',
        'status',
        'settings',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'date',
        'closed_at' => 'date',
        'settings' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Parent is filled only for a Site that lives under an operational Branch. */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_branch_id');
    }

    public function sites(): HasMany
    {
        return $this->hasMany(self::class, 'parent_branch_id');
    }

public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

   
   /*
|--------------------------------------------------------------------------
| Scope
|--------------------------------------------------------------------------
*/

public function scopeForCompany(Builder $query, int $companyId): Builder
{
    return $query->where('company_id', $companyId);
}

public function scopeActive(Builder $query): Builder
{
    return $query->where('status', 'active');
}
}
