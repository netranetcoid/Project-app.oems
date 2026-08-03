<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceShift;
use App\Models\AttendanceShiftAssignment;
use App\Models\Branch;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceShiftAssignmentController extends Controller
{
    public function index(): View
    {
        $companyId = (int) session('company_id');
        $today = now()->toDateString();

        $employees = Employee::query()
            ->forCompany($companyId)
            ->with(['branch:id,name', 'division:id,name', 'position:id,name'])
            ->orderBy('name')
            ->get();

        $currentAssignments = AttendanceShiftAssignment::query()
            ->company($companyId)->active()->with('shift:id,name,settings,code')
            ->whereDate('start_date', '<=', $today)
            ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', $today))
            ->orderByDesc('start_date')->get()->unique('employee_id')->keyBy('employee_id');

        $assignments = AttendanceShiftAssignment::query()
            ->company($companyId)
            ->with(['employee:id,employee_no,name,branch_id,division_id,position_id', 'employee.branch:id,name', 'employee.division:id,name', 'employee.position:id,name', 'branch:id,name', 'shift:id,name,code,settings'])
            ->latest('start_date')->latest('id')->paginate(15);

        return view('attendance.shift-assignments.index', compact('employees', 'currentAssignments', 'assignments'));
    }

    public function create(): View
    {
        return $this->formView();
    }

    /** Backward-compatible single-day endpoint. */
    public function store(Request $request): RedirectResponse
    {
        return $this->storeWeekly($request);
    }

    /** Saves one employee's Monday-Sunday plan as seven date-specific assignments. */
    public function storeWeekly(Request $request): RedirectResponse
    {
        $companyId = (int) session('company_id');
        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'week_start' => ['required', 'date'],
            'days' => ['required', 'array', 'size:7'],
            'days.*' => ['required'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $employee = Employee::query()->forCompany($companyId)->findOrFail($data['employee_id']);
        $branchId = $data['branch_id'] ?: $employee->branch_id;
        if ($branchId) {
            Branch::query()->forCompany($companyId)->findOrFail($branchId);
        }

        $dayOff = $this->ensureDayOffShift($companyId);
        $weekStart = Carbon::parse($data['week_start'])->startOfDay();

        foreach ($data['days'] as $date => $shiftValue) {
            $dateObject = Carbon::parse($date)->startOfDay();
            abort_unless($dateObject->betweenIncluded($weekStart, $weekStart->copy()->addDays(6)), 422);

            $shift = ((string) $shiftValue === 'off')
                ? $dayOff
                : AttendanceShift::query()->company($companyId)->active()->findOrFail((int) $shiftValue);

            $assignment = AttendanceShiftAssignment::withTrashed()->firstOrNew([
                'company_id' => $companyId,
                'employee_id' => $employee->id,
                'start_date' => $dateObject->toDateString(),
            ]);
            if ($assignment->trashed()) {
                $assignment->restore();
            }
            $assignment->fill([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'employee_id' => $employee->id,
                'attendance_shift_id' => $shift->id,
                'start_date' => $dateObject->toDateString(),
                'end_date' => $dateObject->toDateString(),
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ])->save();
        }

        return redirect()->route('attendance.shift-assignments.index')
            ->with('success', 'Jadwal mingguan berhasil disimpan untuk 7 hari.');
    }

    public function edit(AttendanceShiftAssignment $assignment): View
    {
        $this->guardAssignment($assignment);
        return $this->formView($assignment);
    }

    public function update(Request $request, AttendanceShiftAssignment $assignment): RedirectResponse
    {
        $this->guardAssignment($assignment);
        if (!$request->has('days')) {
            $companyId = (int) session('company_id');
            $data = $request->validate([
                'employee_id' => ['required', 'integer'],
                'branch_id' => ['nullable', 'integer'],
                'attendance_shift_id' => ['required', 'integer'],
                'start_date' => ['required', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'status' => ['required', 'in:active,inactive'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ]);
            $employee = Employee::query()->forCompany($companyId)->findOrFail($data['employee_id']);
            $shift = AttendanceShift::query()->company($companyId)->active()->findOrFail($data['attendance_shift_id']);
            $assignment->fill([
                'company_id' => $companyId,
                'branch_id' => $data['branch_id'] ?: $employee->branch_id,
                'employee_id' => $employee->id,
                'attendance_shift_id' => $shift->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ])->save();
            return redirect()->route('attendance.shift-assignments.index')
                ->with('success', 'Penugasan shift diperbarui.');
        }
        return $this->storeWeekly($request);
    }

    public function destroy(AttendanceShiftAssignment $assignment): RedirectResponse
    {
        $this->guardAssignment($assignment);
        $assignment->delete();
        return redirect()->route('attendance.shift-assignments.index')->with('success', 'Jadwal dihapus.');
    }

    private function formView(?AttendanceShiftAssignment $assignment = null): View
    {
        $companyId = (int) session('company_id');
        $employees = Employee::query()->forCompany($companyId)->with(['branch:id,name', 'division:id,name', 'position:id,name'])->orderBy('name')->get();
        $branches = Branch::query()->forCompany($companyId)->orderBy('name')->get(['id', 'name', 'type']);
        $shifts = AttendanceShift::query()->company($companyId)->active()->orderBy('name')->get(['id', 'name', 'code', 'branch_id', 'settings']);
        $dayOff = $this->ensureDayOffShift($companyId);
        $shiftOptions = $shifts->map(function (AttendanceShift $shift): array {
            return [
                'id' => $shift->id,
                'name' => $shift->name,
                'code' => $shift->code,
                'day_off' => (bool) $shift->is_day_off,
            ];
        })->values()->all();
        $weekStart = old('week_start');
        if (!$weekStart) {
            $weekStart = $assignment?->start_date
                ? Carbon::parse($assignment->start_date)->startOfWeek(Carbon::MONDAY)->format('Y-m-d')
                : now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        }

        return view('attendance.shift-assignments.create', compact('assignment', 'employees', 'branches', 'shifts', 'dayOff', 'weekStart', 'shiftOptions'));
    }

    private function ensureDayOffShift(int $companyId): AttendanceShift
    {
        $shift = AttendanceShift::withTrashed()->firstOrNew(['company_id' => $companyId, 'code' => 'OFF']);
        if ($shift->trashed()) {
            $shift->restore();
        }
        $settings = (array) ($shift->settings ?? []);
        $shift->fill([
            'company_id' => $companyId, 'branch_id' => null, 'code' => 'OFF', 'name' => 'Day Off',
            'work_type' => 'flexible', 'clock_in_time' => '00:00', 'clock_out_time' => '00:00',
            'work_hours' => 0, 'grace_in_minutes' => 0, 'grace_out_minutes' => 0,
            'late_tolerance_minutes' => 0, 'allow_overtime' => false,
            'gps_required' => false, 'selfie_required' => false, 'photo_required' => false,
            'status' => 'active', 'settings' => array_merge($settings, ['day_off' => true]),
            'notes' => 'Hari libur / bukan hari kerja.',
        ])->save();
        return $shift;
    }

    private function guardAssignment(AttendanceShiftAssignment $assignment): void
    {
        abort_unless((int) $assignment->company_id === (int) session('company_id'), 404);
    }
}
