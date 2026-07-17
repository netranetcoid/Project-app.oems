<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceShift;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AttendanceShiftController extends Controller
{
    public function index(): View
    {
        $companyId = session('company_id');

        $shifts = AttendanceShift::with('branch')
            ->where('company_id', $companyId)
            ->latest()
            ->paginate(10);

        return view('attendance.shifts.index', compact('shifts'));
    }

    public function create(): View
    {
        $companyId = session('company_id');

        $branches = Branch::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('attendance.shifts.create', compact('branches'));
    }

   public function store(Request $request): RedirectResponse
{
    $companyId = session('company_id');

    $request->validate([
        'branch_id' => 'nullable|exists:branches,id',
        'code' => 'required|max:20',
        'name' => 'required|max:100',
        'work_type' => 'required',
        'clock_in_time' => 'required',
        'clock_out_time' => 'required',
        'break_start_time' => 'nullable',
        'break_end_time' => 'nullable',
        'work_hours' => 'required|numeric',
        'grace_in_minutes' => 'nullable|integer',
        'grace_out_minutes' => 'nullable|integer',
        'late_tolerance_minutes' => 'nullable|integer',
        'overtime_after_minutes' => 'nullable|integer',
        'status' => 'required',
    ]);

    AttendanceShift::create([
        'company_id' => $companyId,
        'branch_id' => $request->branch_id,
        'code' => $request->code,
        'name' => $request->name,
        'work_type' => $request->work_type,

        'clock_in_time' => $request->clock_in_time,
        'clock_out_time' => $request->clock_out_time,

        'break_start' => $request->break_start_time,
        'break_end' => $request->break_end_time,

        'work_hours' => $request->work_hours,

        'grace_in_minutes' => $request->grace_in_minutes ?? 0,
        'grace_out_minutes' => $request->grace_out_minutes ?? 0,

        'late_tolerance_minutes' => $request->late_tolerance_minutes ?? 0,

        'allow_overtime' => $request->has('allow_overtime'),

        'overtime_after_minutes' => $request->overtime_after_minutes ?? 0,

        'gps_required' => $request->has('gps_required'),
        'selfie_required' => $request->has('selfie_required'),
        'photo_required' => $request->has('photo_required'),

        'status' => $request->status,
        'notes' => $request->notes,
    ]);

    return redirect()
        ->route('attendance.shifts.index')
        ->with('success', 'Shift berhasil disimpan.');
}
public function edit(AttendanceShift $shift): View
{
    $companyId = session('company_id');

    abort_if($shift->company_id != $companyId, 403);

    $branches = Branch::where('company_id', $companyId)
        ->orderBy('name')
        ->get();

    return view('attendance.shifts.edit', compact(
        'shift',
        'branches'
    ));
}

public function update(Request $request, AttendanceShift $shift): RedirectResponse
{
    return redirect()
        ->route('attendance.shifts.index')
        ->with('success', 'Shift berhasil diperbarui.');
}

public function destroy(AttendanceShift $shift): RedirectResponse
{
    $companyId = session('company_id');

    abort_if($shift->company_id != $companyId, 403);

    $shift->delete();

    return redirect()
        ->route('attendance.shifts.index')
        ->with('success', 'Shift berhasil dihapus.');
}
}
