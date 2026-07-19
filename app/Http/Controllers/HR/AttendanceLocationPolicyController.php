<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocationPolicy;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Central developer-only dashboard for all attendance geofences. HR may use
 * the policies, but cannot silently weaken or move an attendance boundary.
 */
class AttendanceLocationPolicyController extends Controller
{
    public function index(Request $request): View
    {
        $this->assertDeveloper($request);
        $company = $this->company();

        return view('hr.attendance-locations.index', [
            'company' => $company,
            'branches' => Branch::query()->forCompany($company->id)->active()->orderBy('name')->get(),
            'divisions' => Division::query()->forCompany($company->id)->active()->orderBy('name')->get(),
            'policies' => AttendanceLocationPolicy::query()->forCompany($company->id)
                ->orderByRaw("CASE scope_type WHEN 'company' THEN 1 WHEN 'branch' THEN 2 ELSE 3 END")
                ->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->assertDeveloper($request);
        $data = $this->validated($request);
        AttendanceLocationPolicy::query()->create($data + [
            'company_id' => $this->company()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Kebijakan lokasi presensi berhasil disimpan.');
    }

    public function update(Request $request, AttendanceLocationPolicy $policy): RedirectResponse
    {
        $this->assertDeveloper($request);
        abort_unless((int) $policy->company_id === (int) $this->company()->id, 404);
        $policy->update($this->validated($request, $policy) + ['updated_by' => $request->user()->id]);

        return back()->with('success', 'Kebijakan lokasi presensi berhasil diperbarui.');
    }

    public function destroy(Request $request, AttendanceLocationPolicy $policy): RedirectResponse
    {
        $this->assertDeveloper($request);
        abort_unless((int) $policy->company_id === (int) $this->company()->id, 404);
        $policy->delete();

        return back()->with('success', 'Kebijakan lokasi dihapus. Sistem kembali memakai fallback branch/kantor utama.');
    }

    private function validated(Request $request, ?AttendanceLocationPolicy $policy = null): array
    {
        $companyId = $this->company()->id;
        $data = $request->validate([
            'scope_type' => ['required', Rule::in(['company', 'branch', 'division'])],
            'scope_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:120'],
            'mode' => ['required', Rule::in(['geofence', 'anywhere'])],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meter' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['scope_type'] === 'company') {
            $data['scope_id'] = null;
        } elseif ($data['scope_type'] === 'branch') {
            abort_unless(Branch::query()->forCompany($companyId)->whereKey($data['scope_id'])->exists(), 422);
        } else {
            abort_unless(Division::query()->forCompany($companyId)->whereKey($data['scope_id'])->exists(), 422);
        }

        if ($data['mode'] === 'geofence' && (! isset($data['latitude'], $data['longitude'], $data['radius_meter']))) {
            return back()->withErrors(['latitude' => 'Latitude, longitude, dan radius wajib untuk mode Geofence.'])->throwResponse();
        }

        $duplicate = AttendanceLocationPolicy::query()->forCompany($companyId)
            ->where('scope_type', $data['scope_type'])
            ->where($data['scope_id'] === null ? 'scope_id' : 'scope_id', $data['scope_id']);
        if ($policy) {
            $duplicate->whereKeyNot($policy->id);
        }
        if ($duplicate->exists()) {
            return back()->withErrors(['scope_id' => 'Scope ini sudah memiliki satu kebijakan lokasi. Edit kebijakan yang ada.'])->throwResponse();
        }

        $data['is_active'] = $request->boolean('is_active');
        if ($data['mode'] === 'anywhere') {
            $data['latitude'] = null;
            $data['longitude'] = null;
            $data['radius_meter'] = null;
        }

        return $data;
    }

    private function assertDeveloper(Request $request): void
    {
        abort_unless((bool) $request->user()?->is_developer, 403);
    }

    private function company(): Company
    {
        return Company::query()->findOrFail((int) session('company_id'));
    }
}
