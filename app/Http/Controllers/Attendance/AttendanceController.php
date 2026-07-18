<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Dashboard master absensi untuk HR/management.
 *
 * Presensi tetap dibuat oleh API OvallHR; halaman ini hanya membaca rekap,
 * meninjau bukti, dan melakukan approval/rejection yang tercatat ke AppBill
 * melalui AttendanceObserver bila integrasi aktif.
 */
class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = (int) session('company_id');
        $filters = $request->validate([
            'date' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:all,present,late,incomplete,pending,rejected'],
        ]);
        $date = Carbon::parse($filters['date'] ?? today())->startOfDay();
        $branchId = isset($filters['branch_id']) ? (int) $filters['branch_id'] : null;

        // Satu base query dipakai oleh kartu statistik dan tabel. Ini penting
        // supaya angka ringkasan selalu sesuai filter tanggal/site di layar.
        $recordsQuery = Attendance::query()
            ->with(['employee.branch', 'shift'])
            ->where('company_id', $companyId)
            ->whereDate('date', $date->toDateString());
        if ($branchId) {
            $recordsQuery->whereHas('employee', fn ($employee) => $employee->where('branch_id', $branchId));
        }

        $expectedQuery = Employee::query()
            ->forCompany($companyId)
            ->active()
            ->where('is_attendance_required', true);
        if ($branchId) {
            $expectedQuery->where('branch_id', $branchId);
        }

        $stats = [
            'expected' => (clone $expectedQuery)->count(),
            'checked_in' => (clone $recordsQuery)->whereNotNull('clock_in_at')->count(),
            'completed' => (clone $recordsQuery)->whereNotNull('clock_out_at')->count(),
            'late' => (clone $recordsQuery)->where('status', 'late')->count(),
            'pending_review' => (clone $recordsQuery)->whereIn('approval_status', ['pending', 'rejected'])->count(),
        ];
        $stats['not_checked_in'] = max(0, $stats['expected'] - $stats['checked_in']);

        $records = $recordsQuery
            ->when(($filters['status'] ?? 'all') !== 'all', function ($query) use ($filters): void {
                match ($filters['status']) {
                    'incomplete' => $query->whereNotNull('clock_in_at')->whereNull('clock_out_at'),
                    'pending', 'rejected' => $query->where('approval_status', $filters['status']),
                    default => $query->where('status', $filters['status']),
                };
            })
            ->orderByDesc('clock_in_at')
            ->paginate(25)
            ->withQueryString();

        // Nilai ini dihitung server agar tampilan tabel, ekspor kelak, dan API
        // AppBill mempunyai definisi keterlambatan yang sama dengan shift.
        $records->getCollection()->transform(function (Attendance $attendance): Attendance {
            $attendance->setAttribute('late_minutes', $this->lateMinutes($attendance));
            $attendance->setAttribute('work_minutes', $this->workMinutes($attendance));
            return $attendance;
        });

        return view('attendance.index', [
            'date' => $date,
            'branches' => Branch::query()->forCompany($companyId)->active()->orderBy('name')->get(),
            'filters' => $filters,
            'stats' => $stats,
            'records' => $records,
        ]);
    }

    /** Stream bukti dari storage hanya bila record milik company aktif. */
    public function proof(Attendance $attendance, string $direction)
    {
        $this->ensureCompany($attendance);
        abort_unless(in_array($direction, ['in', 'out'], true), 404);
        $path = $direction === 'in' ? $attendance->in_photo : $attendance->out_photo;
        abort_unless($path && Storage::disk('public')->exists($path), 404, 'Bukti presensi tidak tersedia atau sudah melewati retention.');

        return Storage::disk('public')->response($path);
    }

    /** HR menyetujui record yang sebelumnya ditahan/review. */
    public function approve(Attendance $attendance): RedirectResponse
    {
        $this->ensureCompany($attendance);
        $attendance->update([
            'approval_status' => 'approved',
            'rejection_reason' => null,
            'change_reason' => 'Disetujui HR dari dashboard absensi.',
        ]);

        return back()->with('success', 'Presensi disetujui. Perubahan siap disinkronkan ke AppBill bila integrasi live.');
    }

    /** Rejection wajib memiliki alasan agar karyawan dan audit trail jelas. */
    public function reject(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->ensureCompany($attendance);
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $attendance->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $data['reason'],
            'change_reason' => 'Ditolak HR dari dashboard absensi.',
        ]);

        return back()->with('success', 'Presensi ditolak dan alasan tersimpan di audit trail.');
    }

    private function ensureCompany(Attendance $attendance): void
    {
        abort_unless((int) $attendance->company_id === (int) session('company_id'), 404);
    }

    private function lateMinutes(Attendance $attendance): int
    {
        if (! $attendance->clock_in_at || ! $attendance->shift?->clock_in_time) {
            return 0;
        }

        $expected = Carbon::parse($attendance->date->toDateString() . ' ' . $attendance->shift->clock_in_time)
            ->addMinutes((int) $attendance->shift->grace_in_minutes);

        return $attendance->clock_in_at->greaterThan($expected)
            ? $expected->diffInMinutes($attendance->clock_in_at)
            : 0;
    }

    private function workMinutes(Attendance $attendance): int
    {
        if (! $attendance->clock_in_at) {
            return 0;
        }

        return max(0, $attendance->clock_in_at->diffInMinutes($attendance->clock_out_at ?: now()));
    }
}
