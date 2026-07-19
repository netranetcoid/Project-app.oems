<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Data internal kesiapan pendaftaran BPJS PT OSM.
 * Tidak mengirim data ke BPJS dan tidak menggantikan formulir/portal resmi.
 */
class BpjsReadinessProfile extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'target_registration_date' => 'date',
        'submitted_at' => 'date',
        'activated_at' => 'date',
        'document_checklist' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
