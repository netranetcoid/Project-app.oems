<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceShiftAssignment;
use App\Models\AttendanceShift;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AttendanceShiftAssignmentController extends Controller
{
    public function index(): View
    {
        $companyId = session('company_id');

        $assignments = AttendanceShiftAssignment::with([
            'employee',
            'branch',
            'shift'
        ])
        ->where('company_id', $companyId)
        ->latest()
        ->paginate(10);

        return view(
            'attendance.shift-assignments.index',
            compact('assignments')
        );
    }

    public function create(): View
    {
        $companyId = session('company_id');

        // Employee memakai work_status, bukan kolom status. Menggunakan scope
        // mencegah error SQL saat membuka form penugasan jadwal shift.
        $employees = Employee::where('company_id', $companyId)
            ->active()
            ->get()
            ->sortBy('full_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $branches = Branch::where('company_id',$companyId)
            ->orderBy('name')
            ->get();

        $shifts = AttendanceShift::where('company_id',$companyId)
            ->active()
            ->orderBy('name')
            ->get();

        return view(
            'attendance.shift-assignments.create',
            compact(
                'employees',
                'branches',
                'shifts'
            )
        );
    }

   public function store(Request $request): RedirectResponse
{
    $companyId = session('company_id');

    $request->validate([
        'branch_id' => 'nullable|exists:branches,id',
        'employee_id' => 'required|exists:employees,id',
        'attendance_shift_id' => 'required|exists:attendance_shifts,id',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'status' => 'required|in:active,inactive',
        'notes' => 'nullable|string',
    ]);

    AttendanceShiftAssignment::create([
        'company_id' => $companyId,
        'branch_id' => $request->branch_id,
        'employee_id' => $request->employee_id,
        'attendance_shift_id' => $request->attendance_shift_id,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'status' => $request->status,
        'notes' => $request->notes,
    ]);

    return redirect()
        ->route('attendance.shift-assignments.index')
        ->with('success', 'Jadwal Shift berhasil disimpan.');
}

    public function edit(
        AttendanceShiftAssignment $assignment
    ): View
    {
        //
    }

    public function update(
        Request $request,
        AttendanceShiftAssignment $assignment
    ): RedirectResponse
    {
        //
    }

    public function destroy(
        AttendanceShiftAssignment $assignment
    ): RedirectResponse
    {
        //
    }
}
