<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Company extends Model
{
  protected $guarded = ['id'];

  protected $casts = [
    'is_active' => 'boolean',
    'settings' => 'array',
  ];

  public function users(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'company_user')
      ->withPivot(['is_default', 'is_active'])
      ->withTimestamps();
  }

  public function branches(): HasMany
  {
    return $this->hasMany(Branch::class);
  }

  public function divisions(): HasMany
  {
    return $this->hasMany(Division::class);
  }

  public function positions(): HasMany
  {
    return $this->hasMany(Position::class);
  }

  public function employees(): HasMany
  {
    return $this->hasMany(Employee::class);
  }

  public function scopeActive(Builder $query): Builder
  {
    if (Schema::hasColumn($this->getTable(), 'is_active')) {
      $query->where($this->getTable() . '.is_active', true);
    }

    if (Schema::hasColumn($this->getTable(), 'status')) {
      $query->where($this->getTable() . '.status', 'active');
    }

    return $query;
  }
}
