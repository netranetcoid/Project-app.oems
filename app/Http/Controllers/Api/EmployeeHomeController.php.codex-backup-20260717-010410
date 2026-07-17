<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeHomeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $employee = $request->user()?->employee;
        abort_unless($employee && (int) $employee->company_id === (int) $request->user()->company_id, 403, 'Akun tidak terhubung ke data karyawan.');
        $employee->load(['company', 'branch', 'division', 'position']);
        $attendance = Attendance::query()->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)->whereDate('date', today())->with('shift')->first();
        $workMinutes = ($attendance?->clock_in_at && $attendance?->clock_out_at)
            ? $attendance->clock_in_at->diffInMinutes($attendance->clock_out_at) : 0;

        return response()->json(['data' => [
            'employee' => [
                'id' => $employee->id, 'name' => $employee->name,
                'employee_code' => $employee->employee_no,
                'position' => $employee->position?->name,
                'department' => $employee->division?->name,
                'company' => $employee->branch?->name ?: $employee->company?->name,
                'avatar_url' => $employee->photo_url,
            ],
            'attendance' => [
                'clock_in' => $attendance?->clock_in_at?->format('H:i'),
                'clock_out' => $attendance?->clock_out_at?->format('H:i'),
                'shift' => $attendance?->shift?->name ?? 'Belum ada jadwal',
                'work_hours' => sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60),
            ],
            // Saldo cuti siap dihubungkan ke ledger cuti pada iterasi kebijakan.
            'leave_balance' => 0,
            'announcements' => [],
        ]]);
    }
}
