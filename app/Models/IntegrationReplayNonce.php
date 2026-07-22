<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Penyimpanan nonce berumur singkat untuk anti replay request AppBill. */
class IntegrationReplayNonce extends Model
{
    public const UPDATED_AT = null;
    protected $guarded = ['id'];
    protected $casts = ['request_timestamp' => 'datetime', 'expires_at' => 'datetime', 'created_at' => 'datetime'];
}
