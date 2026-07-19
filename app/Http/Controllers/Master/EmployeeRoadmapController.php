<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class EmployeeRoadmapController extends Controller
{
    /**
     * Menampilkan acuan perjalanan karyawan PT OSM.
     *
     * Halaman ini bersifat informasi kebijakan: tidak mengubah status pegawai,
     * kontrak, payroll, atau keputusan HR. Penetapan status tetap dilakukan
     * lewat proses kontrak dan approval yang sudah memiliki audit trail.
     */
    public function index(): View
    {
        return view('master.employee-roadmap.index', [
            'stages' => [
                [
                    'number' => '01',
                    'title' => 'Rekrutmen',
                    'duration' => '1–14 hari',
                    'icon' => 'ti ti-user-search',
                    'color' => 'primary',
                    'status' => 'Calon Karyawan',
                    'items' => ['Seleksi administrasi', 'Interview HR', 'Interview user', 'Tes teknis bila diperlukan', 'Offering letter'],
                    'output' => 'Lulus / Tidak lulus',
                ],
                [
                    'number' => '02',
                    'title' => 'Onboarding',
                    'duration' => '90 hari pertama',
                    'icon' => 'ti ti-rocket',
                    'color' => 'info',
                    'status' => 'Masa adaptasi',
                    'items' => ['Adaptasi budaya perusahaan', 'Memahami SOP', 'Pelatihan pekerjaan', 'Penilaian karakter', 'Penilaian disiplin'],
                    'output' => 'Lulus evaluasi / Tidak lulus',
                ],
                [
                    'number' => '03',
                    'title' => 'Status Hubungan Kerja',
                    'duration' => 'Setelah onboarding',
                    'icon' => 'ti ti-file-certificate',
                    'color' => 'warning',
                    'status' => 'Keputusan perusahaan',
                    'items' => ['PKWT bila sesuai sifat pekerjaan', 'PKWTT bila posisi tetap dan kebutuhan jangka panjang', 'Mengikuti kebutuhan bisnis dan ketentuan hukum'],
                    'output' => 'Kontrak kerja yang sah',
                ],
                [
                    'number' => '04',
                    'title' => 'Pengembangan',
                    'duration' => 'Evaluasi setiap 6 bulan',
                    'icon' => 'ti ti-trending-up',
                    'color' => 'success',
                    'status' => 'Karyawan aktif',
                    'items' => ['KPI dan kehadiran', 'Disiplin dan integritas', 'Teamwork dan kompetensi', 'Pelatihan / sertifikasi', 'Bonus atau penyesuaian sesuai kebijakan'],
                    'output' => 'Rencana pengembangan',
                ],
            ],
            'careerLevels' => ['Junior Staff', 'Staff', 'Senior Staff', 'Supervisor', 'Assistant Manager', 'Manager', 'General Manager', 'Director'],
            'benefitTiers' => [
                ['period' => '0–3 Bulan', 'color' => 'primary', 'items' => ['Pelatihan', 'Evaluasi', 'Seragam', 'ID Card']],
                ['period' => '3–12 Bulan', 'color' => 'info', 'items' => ['Bonus KPI sesuai kebijakan', 'BPJS sesuai ketentuan', 'Pelatihan lanjutan']],
                ['period' => '1 Tahun+', 'color' => 'success', 'items' => ['Program pengembangan karier', 'Peluang promosi', 'Penyesuaian benefit sesuai kebijakan']],
                ['period' => '2 Tahun+', 'color' => 'warning', 'items' => ['Kandidat Senior Staff', 'Mentor karyawan baru', 'Prioritas promosi bila memenuhi syarat']],
                ['period' => '3 Tahun+', 'color' => 'danger', 'items' => ['Kandidat Supervisor', 'Leadership program']],
                ['period' => '5 Tahun+', 'color' => 'secondary', 'items' => ['Talent pool Manager']],
            ],
        ]);
    }
}
