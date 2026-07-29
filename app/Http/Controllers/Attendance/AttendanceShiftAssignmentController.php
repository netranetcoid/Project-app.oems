<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceShift;
use App\Models\AttendanceShiftAssignment;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceShiftAssignmentController extends Controller
{
    /**
     * Jadwal Kerja menampilkan dua sudut pandang: semua pegawai dan
     * daftar penugasan shift. Data selalu dibatasi company aktif di session.
     */
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
            ->company($companyId)
            ->active()
            ->with('shift:id,name')
            ->whereDate('start_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->orderByDesc('start_date')
            ->get()
            ->unique('employee_id')
            ->keyBy('employee_id');

        $assignments = AttendanceShiftAssignment::query()
            ->company($companyId)
            ->with(['employee:id,employee_no,name,branch_id,division_id,position_id', 'employee.branch:id,name', 'employee.division:id,name', 'employee.position:id,name', 'branch:id,name', 'shift:id,name'])
            ->latest('start_date')
            ->latest('id')
            ->paginate(15);

        return view('attendance.shift-assignments.index', compact('employees', 'currentAssignments', 'assignments'));
    }

    /**
     * Jangan memakai scope active() di sini. Data pegawai lama sering belum
     * mempunyai work_status yang seragam, tetapi tetap harus dapat dijadwalkan.
     */
    public function create(): View
    {
        return $this->formView();
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = (int) session('company_id');
        $data = $this->validatedData($request);

        [$employee, $branch, $shift] = $this->resolveCompanyData($companyId, $data);

        // Satu pegawai hanya mempunyai satu jadwal efektif pada tanggal mulai
        // yang sama. Penyimpanan ulang memperbarui jadwal, bukan membuat duplikat.
        $assignment = AttendanceShiftAssignment::withTrashed()->firstOrNew([
            'company_id' => $companyId,
            'employee_id' => $employee->id,
            'start_date' => $data['start_date'],
        ]);

        if ($assignment->trashed()) {
            $assignment->restore();
        }

        $assignment->fill($this->assignmentPayload($data, $employee, $branch, $shift));
        $assignment->save();

        return redirect()->route('attendance.shift-assignments.index')
            ->with('success', 'Jadwal kerja pegawai berhasil disimpan.');
    }

    public function edit(AttendanceShiftAssignment $assignment): View
    {
        $this->guardAssignment($assignment);

        return $this->formView($assignment);
    }

    public function update(Request $request, AttendanceShiftAssignment $assignment): RedirectResponse
    {
        $companyId = (int) session('company_id');
        $this->guardAssignment($assignment);
        $data = $this->validatedData($request);
        [$employee, $branch, $shift] = $this->resolveCompanyData($companyId, $data);

        $duplicate = AttendanceShiftAssignment::query()
            ->company($companyId)
            ->where('employee_id', $employee->id)
            ->whereDate('start_date', $data['start_date'])
            ->whereKeyNot($assignment->id)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'start_date' => 'Pegawai ini sudah memiliki jadwal lain pada tanggal mulai tersebut.',
            ]);
        }

        $assignment->fill($this->assignmentPayload($data, $employee, $branch, $shift));
        $assignment->save();

        return redirect()->route('attendance.shift-assignments.index')
            ->with('success', 'Jadwal kerja pegawai berhasil diperbarui.');
    }

    public function destroy(AttendanceShiftAssignment $assignment): RedirectResponse
    {
        $this->guardAssignment($assignment);
        $assignment->delete();

        return redirect()->route('attendance.shift-assignments.index')
            ->with('success', 'Penugasan shift berhasil dihapus.');
    }

    private function formView(?AttendanceShiftAssignment $assignment = null): View
    {
        $companyId = (int) session('company_id');

        $employees = Employee::query()
            ->forCompany($companyId)
            ->with(['branch:id,name', 'division:id,name', 'position:id,name'])
            ->orderBy('name')
            ->get();

        $branches = Branch::query()
            ->forCompany($companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        $shifts = AttendanceShift::query()
            ->company($companyId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        return view('attendance.shift-assignments.create', compact('assignment', 'employees', 'branches', 'shifts'));
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'employee_id' => ['required', 'integer'],
            'attendance_shift_id' => ['required', 'integer'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /** Company ownership is rechecked server-side; selected IDs cannot cross company. */
    private function resolveCompanyData(int $companyId, array $data): array
    {
        $employee = Employee::query()->forCompany($companyId)->findOrFail($data['employee_id']);
        $shift = AttendanceShift::query()->company($companyId)->active()->findOrFail($data['attendance_shift_id']);

        $branchId = $data['branch_id'] ?? $employee->branch_id;
        $branch = $branchId
            ? Branch::query()->forCompany($companyId)->findOrFail($branchId)
            : null;

        return [$employee, $branch, $shift];
    }

    private function assignmentPayload(array $data, Employee $employee, ?Branch $branch, AttendanceShift $shift): array
    {
        return [
            'company_id' => (int) session('company_id'),
            'branch_id' => $branch?->id ?? $employee->branch_id,
            'employee_id' => $employee->id,
            'attendance_shift_id' => $shift->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function guardAssignment(AttendanceShiftAssignment $assignment): void
    {
        abort_unless($assignment->company_id === (int) session('company_id'), 404);
    }
}