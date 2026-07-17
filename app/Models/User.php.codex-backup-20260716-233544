<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
  // Token API dipakai OvallHR mobile; session web tetap memakai guard web.
  use HasApiTokens;
  use HasFactory;
  use Notifiable;
  use HasRoles;
  use SoftDeletes;

  protected $guard_name = 'web';

  protected $fillable = [
    'company_id',
    'branch_id',
    'division_id',
    'position_id',
    'employee_id',
    'name',
    'email',
    'password',
    'phone',
    'google_id',
    'avatar',
    'status',
    'is_active',
    'is_locked',
    'is_super_admin',
    'is_owner',
    'is_developer',
    'last_login_at',
    'last_login_ip',
    'last_login_user_agent',
    'last_failed_login_at',
    'password_changed_at',
    'locked_at',
    'last_activity_at',
    'preferences',
    'settings',
  ];

  protected $hidden = [
    'password',
    'remember_token',
    'google_token',
    'google_refresh_token',
  ];

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'phone_verified_at' => 'datetime',
      'last_login_at' => 'datetime',
      'last_failed_login_at' => 'datetime',
      'password_changed_at' => 'datetime',
      'locked_at' => 'datetime',
      'last_activity_at' => 'datetime',
      'is_super_admin' => 'boolean',
      'is_owner' => 'boolean',
      'is_developer'  => 'boolean',
      'is_active' => 'boolean',
      'is_locked' => 'boolean',
      'preferences' => 'array',
      'settings' => 'array',
      'password' => 'hashed',
    ];
  }

  public function company(): BelongsTo
  {
    return $this->belongsTo(Company::class, 'company_id');
  }

  public function branch(): BelongsTo
  {
    return $this->belongsTo(Branch::class, 'branch_id');
  }

  public function division(): BelongsTo
  {
    return $this->belongsTo(Division::class, 'division_id');
  }

  public function position(): BelongsTo
  {
    return $this->belongsTo(Position::class, 'position_id');
  }

  public function employee(): HasOne
  {
    return $this->hasOne(Employee::class, 'user_id');
  }

  public function companies(): BelongsToMany
  {
    return $this->belongsToMany(Company::class, 'company_user')
      ->withPivot(['is_default', 'is_active'])
      ->withTimestamps();
  }

  public function activeCompanies(): BelongsToMany
  {
    return $this->companies()
      ->wherePivot('is_active', true)
      ->where(function ($query) {
        if (Schema::hasColumn('companies', 'is_active')) {
          $query->where('companies.is_active', true);
        }

        if (Schema::hasColumn('companies', 'status')) {
          $query->where('companies.status', 'active');
        }
      });
  }

  public function isActiveUser(): bool
  {
    if (Schema::hasColumn($this->getTable(), 'is_active') && !$this->is_active) {
      return false;
    }

    if (Schema::hasColumn($this->getTable(), 'status') && $this->status !== 'active') {
      return false;
    }

    return true;
  }

  public function isLockedUser(): bool
  {
    if (Schema::hasColumn($this->getTable(), 'is_locked') && $this->is_locked) {
      return true;
    }

    if (Schema::hasColumn($this->getTable(), 'locked_at') && !blank($this->locked_at)) {
      return true;
    }

    return false;
  }

  public function isDeveloper(): bool
{
    return (bool) $this->is_developer;
}

public function canBeDeleted(): bool
{
    return !$this->is_developer;
}

public function canBeLocked(): bool
{
    return !$this->is_developer;
}

public function canSyncAppBill(): bool
{
    return !$this->is_developer;
}

  protected function statusLabel(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->isActiveUser() && !$this->isLockedUser() ? 'Active' : 'Inactive'
    );
  }
}
