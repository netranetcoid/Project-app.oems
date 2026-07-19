<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\User;

class Employee extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [

        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        'birth_date'            => 'date',

        'join_date'             => 'date',

        'probation_end_date'    => 'date',

        'contract_start_date'   => 'date',

        'contract_end_date'     => 'date',

        'resign_date'           => 'date',

        // Kesiapan BPJS dipakai HR saat onboarding dan rekonsiliasi F1a.
        'bpjs_effective_date'   => 'date',

        /*
        |--------------------------------------------------------------------------
        | Salary
        |--------------------------------------------------------------------------
        */

        'basic_salary'          => 'decimal:2',

        'daily_salary'          => 'decimal:2',

        'hourly_salary'         => 'decimal:2',

        'fixed_allowance'       => 'decimal:2',

        'meal_allowance'        => 'decimal:2',

        'transport_allowance'   => 'decimal:2',

        'position_allowance'    => 'decimal:2',

        'kpi_incentive_max'     => 'decimal:2',

        /*
        |--------------------------------------------------------------------------
        | Boolean
        |--------------------------------------------------------------------------
        */

        'is_kpi_enabled'                => 'boolean',

        'is_bpjs_kesehatan_active'      => 'boolean',

        'is_bpjs_ketenagakerjaan_active'=> 'boolean',

        'is_attendance_required'        => 'boolean',

        /*
        |--------------------------------------------------------------------------
        | JSON
        |--------------------------------------------------------------------------
        */

        'custom_fields' => 'array',

        'settings'      => 'array',

    ];

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForCompany(
        Builder $query,
        int $companyId
    ): Builder {

        return $query->where(
            'company_id',
            $companyId
        );

    }

    public function scopeForBranch(
        Builder $query,
        ?int $branchId
    ): Builder {

        if ($branchId) {

            $query->where(
                'branch_id',
                $branchId
            );

        }

        return $query;

    }

    public function scopeForDivision(
        Builder $query,
        ?int $divisionId
    ): Builder {

        if ($divisionId) {

            $query->where(
                'division_id',
                $divisionId
            );

        }

        return $query;

    }

    public function scopeForPosition(
        Builder $query,
        ?int $positionId
    ): Builder {

        if ($positionId) {

            $query->where(
                'position_id',
                $positionId
            );

        }

        return $query;

    }

    public function scopeActive(
        Builder $query
    ): Builder {

        return $query->where(
            'work_status',
            'active'
        );

    }

    public function scopePermanent(
        Builder $query
    ): Builder {

        return $query->where(
            'employment_status',
            'permanent'
        );

    }

    public function scopeContract(
        Builder $query
    ): Builder {

        return $query->where(
            'employment_status',
            'contract'
        );

    }

    public function scopeProbation(
        Builder $query
    ): Builder {

        return $query->where(
            'employment_status',
            'probation'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Relationship
    |--------------------------------------------------------------------------
    */

        /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Branch (Site)
    |--------------------------------------------------------------------------
    */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(
            Branch::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Division
    |--------------------------------------------------------------------------
    */

    public function division(): BelongsTo
    {
        return $this->belongsTo(
            Division::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Position
    |--------------------------------------------------------------------------
    */

    public function position(): BelongsTo
    {
        return $this->belongsTo(
            Position::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Supervisor
    |--------------------------------------------------------------------------
    */

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'supervisor_employee_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Manager
    |--------------------------------------------------------------------------
    */

    public function manager(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'manager_employee_id'
        );
    }

    /*
|--------------------------------------------------------------------------
| User Login OEMS
|--------------------------------------------------------------------------
*/

public function user(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'user_id'
    );
}
    /*
    |--------------------------------------------------------------------------
    | Direct Subordinates
    |--------------------------------------------------------------------------
    */

    public function subordinates(): HasMany
    {
        return $this->hasMany(
            Employee::class,
            'supervisor_employee_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Direct Reports (Manager)
    |--------------------------------------------------------------------------
    */

    public function managedEmployees(): HasMany
    {
        return $this->hasMany(
            Employee::class,
            'manager_employee_id'
        );
    }

    /** Dokumen identitas/administrasi tersimpan privat dan dibuka berizin. */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Parent Position
    |--------------------------------------------------------------------------
    */

    public function leaderPosition(): ?Position
    {
        return $this->position;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public function hasSupervisor(): bool
    {
        return !is_null(
            $this->supervisor_employee_id
        );
    }

    public function hasManager(): bool
    {
        return !is_null(
            $this->manager_employee_id
        );
    }

    public function isLeader(): bool
    {
        return $this->subordinates()
            ->exists();
    }

    public function isManager(): bool
    {
        return optional(
            $this->position
        )->is_management ?? false;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */
        /*
    |--------------------------------------------------------------------------
    | Display Name
    |--------------------------------------------------------------------------
    */

    public function getDisplayNameAttribute(): string
    {
        return $this->nickname
            ?: $this->name;
    }

    /*
    |--------------------------------------------------------------------------
    | Photo
    |--------------------------------------------------------------------------
    */

    public function getPhotoUrlAttribute(): string
    {
        if (!empty($this->photo)) {

            return asset(
                'storage/' . $this->photo
            );

        }

        return asset(
            'assets/img/avatars/1.png'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Badge
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->work_status) {

            'active' => 'success',

            'inactive' => 'secondary',

            'resign' => 'danger',

            default => 'warning',

        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->work_status) {

            'active' => 'Aktif',

            'inactive' => 'Tidak Aktif',

            'resign' => 'Resign',

            default => '-',

        };
    }

    /*
    |--------------------------------------------------------------------------
    | Employment Badge
    |--------------------------------------------------------------------------
    */

    public function getEmploymentBadgeAttribute(): string
    {
        return match ($this->employment_status) {

            'permanent' => 'success',

            'contract' => 'warning',

            'probation' => 'info',

            default => 'secondary',

        };
    }

    public function getEmploymentLabelAttribute(): string
    {
        return match ($this->employment_status) {

            'permanent' => 'Tetap',

            'contract' => 'Kontrak',

            'probation' => 'Probation',

            'resign' => 'Resign',

            default => '-',

        };
    }

    /*
    |--------------------------------------------------------------------------
    | Full Address
    |--------------------------------------------------------------------------
    */

    public function getFullAddressAttribute(): string
    {
        return collect([

            $this->address,

            $this->village,

            $this->district,

            $this->city,

            $this->province,

            $this->postal_code,

        ])

        ->filter()

        ->implode(', ');
    }

    /*
    |--------------------------------------------------------------------------
    | Contact
    |--------------------------------------------------------------------------
    */

    public function getContactAttribute(): string
    {
        return $this->whatsapp
            ?: ($this->phone ?: '-');
    }

    /*
    |--------------------------------------------------------------------------
    | Salary
    |--------------------------------------------------------------------------
    */

    public function getTotalAllowanceAttribute(): float
    {
        return

            (float) $this->fixed_allowance +

            (float) $this->meal_allowance +

            (float) $this->transport_allowance +

            (float) $this->position_allowance;
    }

    public function getTotalIncomeAttribute(): float
    {
        return

            (float) $this->basic_salary +

            (float) $this->total_allowance;
    }

    /*
    |--------------------------------------------------------------------------
    | Employment Helper
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->work_status === 'active';
    }

    public function isPermanent(): bool
    {
        return $this->employment_status === 'permanent';
    }

    public function isContract(): bool
    {
        return $this->employment_status === 'contract';
    }

    public function isProbation(): bool
    {
        return $this->employment_status === 'probation';
    }

    /*
    |--------------------------------------------------------------------------
    | KPI
    |--------------------------------------------------------------------------
    */

    public function hasKpi(): bool
    {
        return (bool) $this->is_kpi_enabled;
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    public function requiresAttendance(): bool
    {
        return (bool) $this->is_attendance_required;
    }

    /*
    |--------------------------------------------------------------------------
    | Payroll
    |--------------------------------------------------------------------------
    */

    public function hasPayroll(): bool
    {
        return $this->basic_salary > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | End Model
    |--------------------------------------------------------------------------
    */
        /*
    |--------------------------------------------------------------------------
    | Approval Helper
    |--------------------------------------------------------------------------
    */

    public function canApprove(): bool
    {
        return optional(
            $this->position
        )->is_approver ?? false;
    }

    public function isManagement(): bool
    {
        return optional(
            $this->position
        )->is_management ?? false;
    }

    public function isFieldWorker(): bool
    {
        return optional(
            $this->position
        )->is_field_worker ?? false;
    }

    /*
    |--------------------------------------------------------------------------
    | Contract Helper
    |--------------------------------------------------------------------------
    */

    public function isContractExpired(): bool
    {
        if (!$this->contract_end_date) {
            return false;
        }

        return $this->contract_end_date->isPast();
    }

    public function remainingContractDays(): ?int
    {
        if (!$this->contract_end_date) {
            return null;
        }

        return now()->diffInDays(
            $this->contract_end_date,
            false
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Probation Helper
    |--------------------------------------------------------------------------
    */

    public function probationRemainingDays(): ?int
    {
        if (!$this->probation_end_date) {
            return null;
        }

        return now()->diffInDays(
            $this->probation_end_date,
            false
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Helper
    |--------------------------------------------------------------------------
    */

    public function scopeNeedAttendance(
        Builder $query
    ): Builder {

        return $query->where(
            'is_attendance_required',
            true
        );

    }

    public function scopeNeedKpi(
        Builder $query
    ): Builder {

        return $query->where(
            'is_kpi_enabled',
            true
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Serialization
    |--------------------------------------------------------------------------
    */

    protected $appends = [

        'display_name',

        'photo_url',

        'status_badge',

        'status_label',

        'employment_badge',

        'employment_label',

        'full_address',

        'contact',

        'total_allowance',

        'total_income',

    ];

}
