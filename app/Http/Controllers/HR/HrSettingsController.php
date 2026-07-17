<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrSettingsController extends Controller
{
    public function index(): View
    {
        return view('hr.settings.index', ['company' => $this->company()]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('attendance.update') || $request->user()->can('payroll.update'), 403);
        $data = $request->validate([
            'attendance_radius_meter' => ['required', 'integer', 'min:1', 'max:5000'],
            'office_latitude' => ['required', 'numeric', 'between:-90,90'],
            'office_longitude' => ['required', 'numeric', 'between:-180,180'],
            'attendance_retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'salary_payment_day' => ['required', 'integer', 'min:1', 'max:31'],
            'default_currency' => ['required', 'string', 'size:3'],
        ]);

        $company = $this->company();
        $settings = is_array($company->settings) ? $company->settings : [];
        $settings = array_merge($settings, [
            'office_latitude' => (float) $data['office_latitude'],
            'office_longitude' => (float) $data['office_longitude'],
            'attendance_retention_days' => (int) $data['attendance_retention_days'],
            'attendance_selfie_required' => $request->boolean('attendance_selfie_required'),
        ]);

        $company->update([
            'attendance_gps_required' => $request->boolean('attendance_gps_required'),
            'attendance_radius_meter' => (int) $data['attendance_radius_meter'],
            'salary_payment_day' => (int) $data['salary_payment_day'],
            'default_currency' => strtoupper($data['default_currency']),
            'settings' => $settings,
        ]);

        return back()->with('success', 'Aturan HR, absensi, dan payroll berhasil disimpan.');
    }

    private function company(): Company
    {
        return Company::query()->findOrFail((int) session('company_id'));
    }
}
