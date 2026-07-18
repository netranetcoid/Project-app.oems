<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceShiftAssignment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeScheduleController extends Controller
{
    /** Jadwal personal mobile, dibatasi agar data roster karyawan lain tidak bocor. */
    public function __invoke(Request $request): JsonResponse
    {
        $employee = $request->user()?->employee;
        abort_unless($employee && (int) $employee->company_id === (int) $request->user()->company_id, 403);

        $timezone = $employee->company?->timezone ?: 'Asia/Jakarta';
        $start = Carbon::now($timezone)->startOfDay();
        $end = $start->copy()->addDays(13);
        $assignments = AttendanceShiftAssignment::query()
            ->with('shift')
            ->company((int) $employee->company_id)
            ->active()
            ->where('employee_id', $employee->id)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->where(function ($query) use ($start): void {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', $start->toDateString());
            })
            ->orderBy('start_date')
            ->get()
            ->map(fn (AttendanceShiftAssignment $assignment): array => [
                'shift_code' => $assignment->shift?->code,
                'shift_name' => $assignment->shift?->name ?? 'Shift belum tersedia',
                'start_date' => $assignment->start_date?->toDateString(),
                'end_date' => $assignment->end_date?->toDateString(),
                'start_time' => substr((string) $assignment->shift?->clock_in_time, 0, 5),
                'end_time' => substr((string) $assignment->shift?->clock_out_time, 0, 5),
                'timezone' => $timezone,
            ])->values();

        return response()->json(['data' => ['items' => $assignments, 'timezone' => $timezone]]);
    }
}
