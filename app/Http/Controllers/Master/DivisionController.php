<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DivisionController extends Controller
{
    /**
     * List Division
     */
    public function index(): View
    {
        $divisions = Division::with([
                'company',
                'parent'
            ])
            ->latest()
            ->paginate(10);

        return view('master.Divisions.index', compact('divisions'));
    }

    /**
     * Form Tambah Division
     */
    public function create(): View
    {
        $companies = Company::active()
            ->orderBy('name')
            ->get();

        $parents = Division::active()
            ->orderBy('name')
            ->get();

        return view('master.Divisions.create', compact(
            'companies',
            'parents'
        ));
    }

    /**
     * Simpan Division
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'parent_id' => ['nullable', 'exists:divisions,id'],
            'code' => ['required', 'string', 'max:50', 'unique:divisions,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'head_user_id' => ['nullable', 'exists:users,id'],
            'head_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Division::create($validated);

        return redirect()
            ->route('master.divisions.index')
            ->with('success', 'Division berhasil ditambahkan.');
    }

    /**
     * Form Edit Division
     */
    public function edit(Division $division): View
    {
        $companies = Company::active()
            ->orderBy('name')
            ->get();

        $parents = Division::active()
            ->where('id', '!=', $division->id)
            ->orderBy('name')
            ->get();

        return view('master.Divisions.edit', compact(
            'division',
            'companies',
            'parents'
        ));
    }

    /**
     * Update Division
     */
    public function update(Request $request, Division $division): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'parent_id' => ['nullable', 'exists:divisions,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:divisions,code,' . $division->id,
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'head_user_id' => ['nullable', 'exists:users,id'],
            'head_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $division->update($validated);

        return redirect()
            ->route('master.divisions.index')
            ->with('success', 'Division berhasil diperbarui.');
    }

    /**
     * Hapus Division
     */
    public function destroy(Division $division): RedirectResponse
    {
        $division->delete();

        return redirect()
            ->route('master.divisions.index')
            ->with('success', 'Division berhasil dihapus.');
    }
}