<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
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
        $workMinutes = ($attendance?->clock_in_at && $attendance?->clock_out_at)
            ? $attendance->clock_in_at->diffInMinutes($attendance->clock_out_at) : 0;
        $companySettings = is_array($employee->company?->settings) ? $employee->company->settings : [];
        $birthdaySettings = is_array($companySettings['mobile_birthday'] ?? null) ? $companySettings['mobile_birthday'] : [];
        $isBirthday = $employee->birth_date
            && $employee->birth_date->month === today()->month
            && $employee->birth_date->day === today()->day;

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
            // Hanya pegawai yang berulang tahun menerima ucapan. Tanggal lahir
            // atau nama pegawai lain tidak pernah dibuka ke aplikasi mobile.
            'birthday' => ($isBirthday && ($birthdaySettings['enabled'] ?? true)) ? [
                'title' => str_replace('[[employee_name]]', $employee->name, $birthdaySettings['title'] ?? 'Selamat Ulang Tahun, [[employee_name]]!'),
                'message' => $birthdaySettings['message'] ?? 'Semoga sehat, bahagia, dan semakin sukses bersama perusahaan.',
                'reward_note' => $birthdaySettings['reward_note'] ?? null,
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
