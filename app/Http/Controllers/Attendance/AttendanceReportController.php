<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $report = $this->buildReport($request);

        return view('attendance.report', $report);
    }

    public function export(Request $request): StreamedResponse
    {
        $report = $this->buildReport($request);
        $filename = sprintf('rekap-presensi-%s-%s.csv', $report['start']->format('Ymd'), $report['end']->format('Ymd'));

        return response()->streamDownload(function () use ($report): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['NIK', 'Nama', 'Branch / Site', 'Hari Hadir', 'Terlambat', 'Menit Terlambat', 'Belum Pulang', 'Sakit', 'Izin', 'Cuti', 'Hari Kerja Tercatat', 'Jam Kerja'], ';');
            foreach ($report['rows'] as $row) {
                fputcsv($stream, [
                    $row['employee']->employee_no,
                    $row['employee']->name,
                    $row['employee']->branch?->name ?: '-',
                    $row['present'],
                    $row['late'],
                    $row['late_minutes'],
                    $row['incomplete'],
                    $row['sick_days'],
                    $row['permission_days'],
                    $row['leave_days'],
                    $row['attendance_days'],
                    number_format($row['work_minutes'] / 60, 2, ',', ''),
                ], ';');
            }
            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildReport(Request $request): array
    {
        $companyId = (int) session('company_id');
        $timezone = $this->businessTimezone($companyId);
        $filters = $request->validate([
            'period' => ['nullable', 'in:month,year,custom'],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer'],
            'employee_id' => ['nullable', 'integer'],
        ]);
        [$start, $end, $period] = $this->period($filters, $timezone);
        abort_if($start->diffInDays($end) > 366, 422, 'Rentang rekap maksimal 366 hari.');

        $employees = Employee::query()
            ->forCompany($companyId)
            ->with(['branch', 'division', 'position'])
            ->when($filters['branch_id'] ?? null, fn ($query, $id) => $query->where('branch_id', $id))
            ->when($filters['employee_id'] ?? null, fn ($query, $id) => $query->whereKey($id))
            ->where('work_status', 'active')
            ->orderBy('name')
            ->get();

        $employeeIds = $employees->pluck('id');
        $attendances = Attendance::query()
            ->where('company_id', $companyId)
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('shift')
            ->get()
            ->groupBy('employee_id');
        $requests = EmployeeRequest::query()
            ->forCompany($companyId)
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $end->toDateString())
            ->where(function ($query) use ($start): void {
                $query->whereDate('end_date', '>=', $start->toDateString())
                    ->orWhere(function ($singleDay) use ($start): void {
                        $singleDay->whereNull('end_date')
                            ->whereDate('start_date', '>=', $start->toDateString());
                    });
            })
            ->get()
            ->groupBy('employee_id');

        $rows = $employees->map(function (Employee $employee) use ($attendances, $requests, $start, $end, $timezone): array {
            $records = $attendances->get($employee->id, collect());
            $employeeRequests = $requests->get($employee->id, collect());
            $lateMinutes = $records->sum(fn (Attendance $record) => $this->lateMinutes($record, $timezone));

            return [
                'employee' => $employee,
                'attendance_days' => $records->whereNotNull('clock_in_at')->count(),
                'present' => $records->whereNotNull('clock_in_at')->count(),
                'late' => $records->filter(fn (Attendance $record) => $record->status === 'late' || $this->lateMinutes($record, $timezone) > 0)->count(),
                'late_minutes' => $lateMinutes,
                'incomplete' => $records->whereNotNull('clock_in_at')->whereNull('clock_out_at')->count(),
                'work_minutes' => $records->sum(fn (Attendance $record) => $this->workMinutes($record)),
                'sick_days' => $this->requestDays($employeeRequests, ['sick'], $start, $end),
                'permission_days' => $this->requestDays($employeeRequests, ['permission'], $start, $end),
                'leave_days' => $this->requestDays($employeeRequests, ['leave'], $start, $end),
            ];
        });

        return [
            'rows' => $rows,
            'summary' => [
                'employees' => $rows->count(),
                'present' => $rows->sum('present'),
                'late' => $rows->sum('late'),
                'sick' => $rows->sum('sick_days'),
                'permission' => $rows->sum('permission_days'),
                'leave' => $rows->sum('leave_days'),
            ],
            'start' => $start,
            'end' => $end,
            'period' => $period,
            'filters' => $filters,
            'branches' => Branch::query()->forCompany($companyId)->active()->orderBy('name')->get(),
            'employeeOptions' => Employee::query()->forCompany($companyId)->where('work_status', 'active')->orderBy('name')->get(['id', 'employee_no', 'name']),
        ];
    }

    private function period(array $filters, string $timezone): array
    {
        $period = $filters['period'] ?? 'month';
        if ($period === 'year') {
            $year = (int) ($filters['year'] ?? now($timezone)->year);
            return [Carbon::create($year, 1, 1, 0, 0, 0, $timezone), Carbon::create($year, 12, 31, 23, 59, 59, $timezone), $period];
        }
        if ($period === 'custom') {
            $start = Carbon::parse($filters['start_date'] ?? now($timezone)->startOfMonth()->toDateString(), $timezone)->startOfDay();
            $end = Carbon::parse($filters['end_date'] ?? now($timezone)->toDateString(), $timezone)->endOfDay();
            abort_if($end->lt($start), 422, 'Tanggal akhir tidak boleh sebelum tanggal awal.');
            return [$start, $end, $period];
        }
        $month = Carbon::createFromFormat('Y-m', $filters['month'] ?? now($timezone)->format('Y-m'), $timezone);
        return [$month->copy()->startOfMonth(), $month->copy()->endOfMonth(), 'month'];
    }

    private function requestDays(Collection $requests, array $types, Carbon $start, Carbon $end): int
    {
        return (int) $requests->whereIn('type', $types)->sum(function (EmployeeRequest $request) use ($start, $end): int {
            $from = $request->start_date->copy()->max($start);
            $until = ($request->end_date ?: $request->start_date)->copy()->min($end);
            return $until->lt($from) ? 0 : $from->diffInDays($until) + 1;
        });
    }

    private function lateMinutes(Attendance $attendance, string $timezone): int
    {
        if (! $attendance->clock_in_at || ! $attendance->shift?->clock_in_time) return 0;
        $clockIn = $attendance->clock_in_at->copy()->setTimezone($timezone);
        $expected = Carbon::parse($attendance->date->toDateString().' '.$attendance->shift->clock_in_time, $timezone)
            ->addMinutes((int) $attendance->shift->grace_in_minutes);
        return $clockIn->greaterThan($expected) ? $expected->diffInMinutes($clockIn) : 0;
    }

    private function workMinutes(Attendance $attendance): int
    {
        if (! $attendance->clock_in_at || ! $attendance->clock_out_at) return 0;
        return max(0, $attendance->clock_in_at->diffInMinutes($attendance->clock_out_at));
    }

    private function businessTimezone(int $companyId): string
    {
        $timezone = (string) (Company::query()->find($companyId)?->timezone ?: 'Asia/Jakarta');
        return in_array($timezone, timezone_identifiers_list(), true) ? $timezone : 'Asia/Jakarta';
    }
}
