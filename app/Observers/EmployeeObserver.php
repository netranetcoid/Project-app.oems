<?php

namespace App\Observers;

use App\Models\Employee;
use App\Services\Integration\AppBillAttendanceService;
use Illuminate\Support\Facades\Schema;

class EmployeeObserver
{
    public function __construct(private AppBillAttendanceService $appBill)
    {
    }

    public function created(Employee $employee): void
    {
        if (Schema::hasTable('integration_outbox')) {
            $this->appBill->queueEmployee($employee, 'employee.created');
        }
    }

    public function updated(Employee $employee): void
    {
        if (Schema::hasTable('integration_outbox') && $employee->wasChanged()) {
            $this->appBill->queueEmployee($employee, 'employee.updated');
        }
    }
}
