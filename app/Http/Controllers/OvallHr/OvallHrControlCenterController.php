<?php

namespace App\Http\Controllers\OvallHr;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MobileAnnouncement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Satu pintu admin untuk seluruh fitur yang dikonsumsi APK OvallHR.
 *
 * Controller ini hanya mengarahkan ke modul sumber yang sudah ada; peraturan,
 * approval, dan validasi asli tetap berada pada controller modul masing-masing.
 * Dengan begitu pusat kontrol tidak menduplikasi aturan bisnis payroll/absensi.
 */
class OvallHrControlCenterController extends Controller
{
    public function index(): View
    {
        $companyId = (int) session('company_id');
        $company = Company::query()->findOrFail($companyId);

        return view('ovallhr.control-center.index', [
            'company' => $company,
            'branding' => $this->branding($company),
            'birthdaySettings' => $this->birthdaySettings($company),
            'announcements' => MobileAnnouncement::query()
                ->where('company_id', $companyId)
                ->latest('published_at')
                ->paginate(8, ['*'], 'announcements_page'),
        ]);
    }

    /** Simpan pengumuman baru yang segera dapat dibaca aplikasi mobile. */
    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        MobileAnnouncement::create([
            'company_id' => (int) session('company_id'),
            'title' => $data['title'],
            'message' => $data['message'],
            'is_active' => true,
            'published_at' => now(),
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return back()->with('success', 'Pengumuman diterbitkan ke OvallHR.');
    }

    /** Nonaktifkan/aktifkan tanpa menghapus riwayat komunikasi HR. */
    public function toggleAnnouncement(MobileAnnouncement $announcement): RedirectResponse
    {
        abort_unless($announcement->company_id === (int) session('company_id'), 404);

        $announcement->update(['is_active' => ! $announcement->is_active]);

        return back()->with('success', 'Status pengumuman berhasil diperbarui.');
    }

    /**
     * Branding remote hanya mengubah tampilan/data konfigurasi, bukan source
     * native. APK yang sudah mendukung remote config akan memuatnya saat login
     * atau aplikasi dibuka kembali tanpa perlu unduhan APK baru.
     */
    public function updateBranding(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:40'],
            'company_label' => ['required', 'string', 'max:120'],
            'welcome_text' => ['nullable', 'string', 'max:160'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo_url' => ['nullable', 'url', 'max:2048'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $company = Company::query()->findOrFail((int) session('company_id'));
        $branding = $this->branding($company);

        if ($request->hasFile('logo')) {
            // File yang diunggah sendiri boleh dibersihkan saat diganti. URL
            // eksternal tidak disentuh agar aset pihak ketiga tidak berisiko.
            if (! empty($branding['logo_path'])) {
                Storage::disk('public')->delete($branding['logo_path']);
            }
            $path = $request->file('logo')->store('mobile-branding/' . $company->id, 'public');
            $branding['logo_path'] = $path;
            $branding['logo_url'] = url(Storage::disk('public')->url($path));
        } elseif (filled($data['logo_url'] ?? null)) {
            $branding['logo_path'] = null;
            $branding['logo_url'] = $data['logo_url'];
        }

        $branding = array_merge($branding, [
            'app_name' => $data['app_name'],
            'company_label' => $data['company_label'],
            'welcome_text' => $data['welcome_text'] ?? null,
            'primary_color' => strtoupper($data['primary_color']),
            'secondary_color' => strtoupper($data['secondary_color']),
        ]);
        $settings = is_array($company->settings) ? $company->settings : [];
        $settings['mobile_branding'] = $branding;
        $company->update(['settings' => $settings]);

        return back()->with('success', 'Branding OvallHR disimpan. Lihat preview lalu terapkan pada APK live.');
    }

    /** Preview browser memakai data yang sama dengan endpoint konfigurasi APK. */
    public function preview(): View
    {
        $company = Company::query()->findOrFail((int) session('company_id'));

        return view('ovallhr.control-center.preview', [
            'company' => $company,
            'branding' => $this->branding($company),
        ]);
    }

    /** Aturan ucapan ulang tahun; tidak membuat bonus payroll otomatis. */
    public function updateBirthdaySettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'birthday_enabled' => ['nullable', 'boolean'],
            'birthday_title' => ['required', 'string', 'max:120'],
            'birthday_message' => ['required', 'string', 'max:500'],
            'birthday_reward_note' => ['nullable', 'string', 'max:300'],
        ]);
        $company = Company::query()->findOrFail((int) session('company_id'));
        $settings = is_array($company->settings) ? $company->settings : [];
        $settings['mobile_birthday'] = [
            'enabled' => $request->boolean('birthday_enabled'),
            'title' => $data['birthday_title'],
            'message' => $data['birthday_message'],
            'reward_note' => $data['birthday_reward_note'] ?? null,
        ];
        $company->update(['settings' => $settings]);

        return back()->with('success', 'Template ulang tahun OvallHR berhasil disimpan.');
    }

    /** Default memastikan APK lama maupun company baru selalu punya tema aman. */
    private function branding(Company $company): array
    {
        $settings = is_array($company->settings) ? $company->settings : [];

        return array_merge([
            'app_name' => 'OvallHR',
            'company_label' => $company->legal_name ?: $company->name,
            'welcome_text' => 'Employee Self Service',
            'primary_color' => '#2563EB',
            'secondary_color' => '#0F2747',
            'logo_url' => null,
            'logo_path' => null,
        ], is_array($settings['mobile_branding'] ?? null) ? $settings['mobile_branding'] : []);
    }

    private function birthdaySettings(Company $company): array
    {
        $settings = is_array($company->settings) ? $company->settings : [];

        return array_merge([
            'enabled' => true,
            'title' => 'Selamat Ulang Tahun, [[employee_name]]!',
            'message' => 'Semoga sehat, bahagia, dan semakin sukses bersama keluarga serta PT Ovall Solusindo Mandiri.',
            'reward_note' => 'Apresiasi ulang tahun akan diinformasikan HR sesuai kebijakan perusahaan.',
        ], is_array($settings['mobile_birthday'] ?? null) ? $settings['mobile_birthday'] : []);
    }
}
