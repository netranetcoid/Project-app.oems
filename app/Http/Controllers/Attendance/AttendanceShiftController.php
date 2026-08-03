<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceShift;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceShiftController extends Controller
{
    public function index(): View
    {
        $companyId = (int) session('company_id');
        $this->ensureDayOffShift($companyId);
        $shifts = AttendanceShift::with('branch')->where('company_id', $companyId)->latest()->paginate(10);
        return view('attendance.shifts.index', compact('shifts'));
    }

    public function create(): View
    {
        $companyId = (int) session('company_id');
        $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();
        return view('attendance.shifts.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = (int) session('company_id');
        $data = $this->validated($request);
        AttendanceShift::create($this->payload($data, $companyId));
        return redirect()->route('attendance.shifts.index')->with('success', 'Shift berhasil disimpan.');
    }

    public function edit(AttendanceShift $shift): View
    {
        $companyId = (int) session('company_id');
        abort_if((int) $shift->company_id !== $companyId, 403);
        $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();
        return view('attendance.shifts.edit', compact('shift', 'branches'));
    }

    public function update(Request $request, AttendanceShift $shift): RedirectResponse
    {
        $companyId = (int) session('company_id');
        abort_if((int) $shift->company_id !== $companyId, 403);
        $shift->update($this->payload($this->validated($request), $companyId));
        return redirect()->route('attendance.shifts.index')->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(AttendanceShift $shift): RedirectResponse
    {
        abort_if((int) $shift->company_id !== (int) session('company_id'), 403);
        abort_if($shift->is_day_off, 422, 'Master Day Off tidak dapat dihapus.');
        $shift->delete();
        return redirect()->route('attendance.shifts.index')->with('success', 'Shift berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'branch_id' => ['nullable', 'integer'], 'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'], 'work_type' => ['required', 'string'],
            'clock_in_time' => ['required'], 'clock_out_time' => ['required'],
            'break_start_time' => ['nullable'], 'break_end_time' => ['nullable'],
            'work_hours' => ['required', 'numeric', 'min:0'], 'grace_in_minutes' => ['nullable', 'integer', 'min:0'],
            'grace_out_minutes' => ['nullable', 'integer', 'min:0'], 'late_tolerance_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_after_minutes' => ['nullable', 'integer', 'min:0'], 'overtime_max_minutes' => ['nullable', 'integer', 'min:1', 'max:720'],
            'status' => ['required', 'in:active,inactive'], 'notes' => ['nullable', 'string'],
        ]);
    }

    private function payload(array $data, int $companyId): array
    {
        return [
            'company_id' => $companyId, 'branch_id' => $data['branch_id'] ?? null, 'code' => $data['code'], 'name' => $data['name'],
            'work_type' => $data['work_type'], 'clock_in_time' => $data['clock_in_time'], 'clock_out_time' => $data['clock_out_time'],
            'break_start' => $data['break_start_time'] ?? null, 'break_end' => $data['break_end_time'] ?? null, 'work_hours' => $data['work_hours'],
            'grace_in_minutes' => $data['grace_in_minutes'] ?? 0, 'grace_out_minutes' => $data['grace_out_minutes'] ?? 0,
            'late_tolerance_minutes' => $data['late_tolerance_minutes'] ?? 0, 'allow_overtime' => request()->boolean('allow_overtime'),
            'overtime_after_minutes' => $data['overtime_after_minutes'] ?? 30, 'overtime_max_minutes' => $data['overtime_max_minutes'] ?? 180,
            'gps_required' => request()->boolean('gps_required'), 'selfie_required' => request()->boolean('selfie_required'),
            'photo_required' => request()->boolean('photo_required'), 'status' => $data['status'], 'notes' => $data['notes'] ?? null,
        ];
    }

    private function ensureDayOffShift(int $companyId): AttendanceShift
    {
        $shift = AttendanceShift::withTrashed()->firstOrNew(['company_id' => $companyId, 'code' => 'OFF']);
        if ($shift->trashed()) $shift->restore();
        $shift->fill([
            'company_id' => $companyId, 'branch_id' => null, 'code' => 'OFF', 'name' => 'Day Off', 'work_type' => 'flexible',
            'clock_in_time' => '00:00', 'clock_out_time' => '00:00', 'work_hours' => 0, 'grace_in_minutes' => 0,
            'grace_out_minutes' => 0, 'late_tolerance_minutes' => 0, 'allow_overtime' => false, 'gps_required' => false,
            'selfie_required' => false, 'photo_required' => false, 'status' => 'active',
            'settings' => array_merge((array) ($shift->settings ?? []), ['day_off' => true]), 'notes' => 'Hari libur / bukan hari kerja.',
        ])->save();
        return $shift;
    }
}
