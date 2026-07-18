<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Pekerjaan individual; data ini tidak bercampur dengan tiket AppBill. */
class EmployeeTask extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['due_at' => 'datetime', 'started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
