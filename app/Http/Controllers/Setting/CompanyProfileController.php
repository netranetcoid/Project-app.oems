<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Legal/company profile is always read and written only for the selected company. */
class CompanyProfileController extends Controller
{
    public function index(): View
    {
        return view('setting.company-profile.index', [
            'company' => Company::query()->findOrFail((int) session('company_id')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $company = Company::query()->findOrFail((int) session('company_id'));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'industry_type' => ['nullable', 'string', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'nib' => ['nullable', 'string', 'max:100'],
            'siup' => ['nullable', 'string', 'max:100'],
            'tdp' => ['nullable', 'string', 'max:100'],
            'akta_no' => ['nullable', 'string', 'max:100'],
            'akta_date' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile_phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_no' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        // code, timezone, policy payroll, dan integrasi tidak diedit dari profil legal.
        $company->update($data);

        return back()->with('success', 'Profil legal perusahaan berhasil diperbarui.');
    }
}