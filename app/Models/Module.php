<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
  protected $guarded = ['id'];

  protected $casts = [
    'is_active' => 'boolean',
    'is_visible' => 'boolean',
  ];

  public function menus(): HasMany
  {
    return $this->hasMany(Menu::class)->orderBy('sort_order');
  }
}
