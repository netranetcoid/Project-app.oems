<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\MobileAppRelease;
use App\Models\MobileFeatureFlag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MobileReleaseCenterController extends Controller
{
    public function index(): View
    {
        $companyId = (int) session('company_id');
        return view('setting.mobile-releases.index', [
            'releases' => MobileAppRelease::forCompany($companyId)->latest('version_code')->paginate(15),
            'features' => MobileFeatureFlag::forCompany($companyId)->orderBy('name')->get(),
        ]);
    }

    public function storeRelease(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'version_name' => ['required', 'string', 'max:40'],
            'version_code' => ['required', 'integer', 'min:1'],
            'minimum_version_code' => ['required', 'integer', 'min:1'],
            'download_url' => ['nullable', 'url', 'max:2048'],
            // APK boleh diunggah langsung oleh owner agar link update tidak
            // bergantung pada Google Drive atau layanan file pihak ketiga.
            // Sebagian browser/PHP mendeteksi APK sebagai application/zip,
            // bukan Android APK. Ekstensi .apk adalah kontrak upload, lalu
            // Android tetap memverifikasi signature saat instalasi.
            'apk_file' => ['nullable', 'file', 'max:204800', 'extensions:apk'],
            'release_notes' => ['nullable', 'string', 'max:5000'],
            'is_force_update' => ['nullable', 'boolean'],
            'publish_now' => ['nullable', 'boolean'],
        ]);
        $publish = $request->boolean('publish_now');
        $downloadUrl = $data['download_url'] ?? null;
        if ($request->hasFile('apk_file')) {
            // Android APK secara internal memang arsip ZIP. `store()` menebak
            // ekstensi lewat MIME lalu dapat menyimpannya sebagai `.zip`, yang
            // membuat ponsel membuka aplikasi arsip, bukan pemasang Android.
            // Nama fisik selalu dipaksa `.apk`; signature tetap diverifikasi
            // Android saat instalasi.
            $path = $request->file('apk_file')->storeAs(
                'mobile-releases/' . session('company_id'),
                Str::random(40) . '.apk',
                'public'
            );
            // `Storage::url()` dapat menghasilkan URL absolut bila disk public
            // sudah memakai APP_URL (konfigurasi Laravel bawaan). Jangan
            // menempelkan APP_URL untuk kedua kali karena hasilnya menjadi
            // `https://oems...https://oems...` dan Android tidak dapat membuka
            // tautan rilis. Hanya path relatif yang perlu diberi base URL.
            $storageUrl = Storage::disk('public')->url($path);
            $downloadUrl = str_starts_with($storageUrl, 'http://') || str_starts_with($storageUrl, 'https://')
                ? $storageUrl
                : rtrim((string) config('app.url'), '/') . '/' . ltrim($storageUrl, '/');
        }
        MobileAppRelease::updateOrCreate(
            ['company_id' => (int) session('company_id'), 'platform' => 'android', 'version_code' => $data['version_code']],
            [
                'version_name' => $data['version_name'],
                'minimum_version_code' => $data['minimum_version_code'],
                'download_url' => $downloadUrl,
                'release_notes' => $data['release_notes'] ?? null,
                'is_force_update' => $request->boolean('is_force_update'),
                'status' => $publish ? 'published' : 'draft',
                'published_by' => $publish ? $request->user()->id : null,
                'published_at' => $publish ? now() : null,
            ]
        );
        return back()->with('success', $publish ? 'Rilis mobile dipublikasikan.' : 'Draft rilis disimpan.');
    }

    public function publish(MobileAppRelease $release, Request $request): RedirectResponse
    {
        $this->company($release);
        abort_if(blank($release->download_url), 422, 'URL unduhan APK/Play Store wajib diisi sebelum publikasi.');
        $release->update(['status' => 'published', 'published_by' => $request->user()->id, 'published_at' => now()]);
        return back()->with('success', 'Rilis dipublikasikan untuk aplikasi OvallHR.');
    }

    public function saveFeature(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_.-]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'value_json' => ['nullable', 'json'],
        ]);
        MobileFeatureFlag::updateOrCreate(
            ['company_id' => (int) session('company_id'), 'key' => $data['key']],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_enabled' => $request->boolean('is_enabled'),
                'value' => filled($data['value_json'] ?? null) ? json_decode($data['value_json'], true, 512, JSON_THROW_ON_ERROR) : null,
            ]
        );
        return back()->with('success', 'Feature mobile diperbarui tanpa perlu rilis APK.');
    }

    public function toggleFeature(MobileFeatureFlag $feature): RedirectResponse
    {
        $this->company($feature);
        $feature->update(['is_enabled' => ! $feature->is_enabled]);
        return back()->with('success', 'Status feature mobile diperbarui.');
    }

    private function company(MobileAppRelease|MobileFeatureFlag $record): void
    {
        abort_if((int) $record->company_id !== (int) session('company_id'), 403);
    }
}
