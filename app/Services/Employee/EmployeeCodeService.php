<?php

namespace App\Services\Employee;

use App\Models\Employee;
use Carbon\Carbon;

class EmployeeCodeService
{
    /**
     * Generate Employee Number
     *
     * Format :
     *
     * OEMS130726001
     * OEMS130726002
     * OEMS130726003
     */

    public static function generate(
        string $joinDate,
        int $companyId
    ): string {

        $date = Carbon::parse($joinDate);

        $prefix = 'OEMS';

        $dateCode = $date->format('dmy');

        $lastEmployee = Employee::query()
            ->where('company_id', $companyId)
            ->whereDate('join_date', $date->toDateString())
            ->orderByDesc('id')
            ->first();

        $runningNumber = 1;

        if (
            $lastEmployee &&
            !empty($lastEmployee->employee_no)
        ) {

            $runningNumber =
                ((int) substr($lastEmployee->employee_no, -3)) + 1;
        }

        return sprintf(
            '%s%s%03d',
            $prefix,
            $dateCode,
            $runningNumber
        );
    }
}