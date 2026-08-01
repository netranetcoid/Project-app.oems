<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MobileAppRelease;
use App\Models\MobileFeatureFlag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileReleaseController extends Controller
{
    // Endpoint publik hanya membagikan metadata versi dan URL rilis yang
    // sudah dipublikasikan; tidak pernah mengirim data pegawai/perusahaan.
    public function check(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_code' => ['required', 'string', 'max:50'],
            'platform' => ['nullable', 'in:android'],
            'version_code' => ['nullable', 'integer', 'min:1'],
        ]);
        $company = Company::query()->where('code', $data['company_code'])->firstOrFail();
        $release = MobileAppRelease::forCompany($company->id)
            ->where('platform', $data['platform'] ?? 'android')
            ->where('status', 'published')
            ->latest('version_code')
            ->first();

        return response()->json(['data' => [
            'release' => $release ? [
                'version_name' => $release->version_name,
                'version_code' => $release->version_code,
                'minimum_version_code' => $release->minimum_version_code,
                'force_update' => $release->is_force_update,
                'download_url' => $release->download_url,
                'release_notes' => $release->release_notes,
                'published_at' => optional($release->published_at)->toIso8601String(),
            ] : null,
        ]]);
    }

    // Konfigurasi fitur butuh token agar hanya pegawai login yang menerimanya.
    public function config(Request $request): JsonResponse
    {
        $companyId = (int) optional($request->user())->company_id;
        abort_unless($companyId, 403);
        $company = Company::query()->findOrFail($companyId);
        $settings = is_array($company->settings) ? $company->settings : [];
        // Default ini menjaga APK yang baru dipasang tetap punya brand konsisten
        // ketika owner belum pernah membuka pengaturan branding remote.
        $branding = array_merge([
            'app_name' => 'OvallHR',
            'company_label' => $company->legal_name ?: $company->name,
            'welcome_text' => 'Employee Self Service',
            'primary_color' => '#2563EB',
            'secondary_color' => '#0F2747',
            'logo_url' => null,
        ], is_array($settings['mobile_branding'] ?? null) ? $settings['mobile_branding'] : []);
        return response()->json(['data' => [
            'branding' => $branding,
            'features' => MobileFeatureFlag::forCompany($companyId)->get()
                ->map(fn (MobileFeatureFlag $flag): array => [
                    'key' => $flag->key,
                    'enabled' => $flag->is_enabled,
                    'value' => $flag->value,
                ])->all(),
        ]]);
    }
}
