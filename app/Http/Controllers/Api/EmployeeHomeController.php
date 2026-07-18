<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\MobileAnnouncement;
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
        // Selama belum checkout, jam kerja harus tetap bergerak pada dashboard.
        $workMinutes = $attendance?->clock_in_at
            ? max(0, $attendance->clock_in_at->diffInMinutes($attendance->clock_out_at ?: now())) : 0;
        $companySettings = is_array($employee->company?->settings) ? $employee->company->settings : [];
        $birthdaySettings = is_array($companySettings['mobile_birthday'] ?? null) ? $companySettings['mobile_birthday'] : [];
        // Ucapan dibuat sebagai perayaan perusahaan: seluruh pegawai aktif
        // melihat ucapan untuk rekan yang berulang tahun hari ini. Yang dikirim
        // hanya nama, bukan tanggal lahir atau data pribadi lainnya.
        $birthdayPeople = Employee::query()
            ->forCompany((int) $employee->company_id)
            ->active()
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', today()->month)
            ->whereDay('birth_date', today()->day)
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name']);
        $celebrantNames = $birthdayPeople->pluck('name')->filter()->values();

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
                'clock_in_at' => $attendance?->clock_in_at?->toIso8601String(),
                'clock_out_at' => $attendance?->clock_out_at?->toIso8601String(),
                'shift' => $attendance?->shift?->name ?? 'Belum ada jadwal',
                'work_hours' => sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60),
                'work_minutes' => $workMinutes,
            ],
            // Saldo cuti siap dihubungkan ke ledger cuti pada iterasi kebijakan.
            'leave_balance' => 0,
            'birthday' => ($celebrantNames->isNotEmpty() && ($birthdaySettings['enabled'] ?? true)) ? [
                'title' => str_replace('[[employee_name]]', $celebrantNames->implode(', '), $birthdaySettings['title'] ?? 'Selamat Ulang Tahun, [[employee_name]]!'),
                'message' => $birthdaySettings['message'] ?? 'Semoga sehat, bahagia, dan semakin sukses bersama perusahaan.',
                'reward_note' => $birthdaySettings['reward_note'] ?? null,
                'celebrant_names' => $celebrantNames,
            ] : null,
            // Pengumuman dikelola dari OvallHR Control Center. Hanya data
            // aktif yang belum kedaluwarsa boleh muncul pada APK karyawan.
            'announcements' => MobileAnnouncement::query()
                ->where('company_id', $employee->company_id)
                ->activeForMobile()
                ->latest('published_at')
                ->limit(10)
                ->get()
                ->map(fn (MobileAnnouncement $announcement): string => trim($announcement->title . ' — ' . $announcement->message))
                ->values(),
        ]]);
    }
}
