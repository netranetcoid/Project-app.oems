<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BranchController extends Controller
{
    /**
     * List Site
     */
    public function index(): View
    {
        $branches = Branch::with('company')
            ->latest()
            ->paginate(10);

        return view('master.Branches.index', compact('branches'));
    }

    /**
     * Form Tambah Site
     */
    public function create(): View
    {
        $companies = Company::active()
            ->orderBy('name')
            ->get();

        return view('master.Branches.create', compact('companies'));
    }

    /**
     * Simpan Site
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'code'       => ['required', 'string', 'max:50', 'unique:branches,code'],
            'name'       => ['required', 'string', 'max:255'],
            'type'       => ['nullable', 'string', 'max:50'],
            'email'      => ['nullable', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'address'    => ['nullable', 'string'],
            'status'     => ['required', 'in:active,inactive,closed'],
        ]);

        Branch::create($validated);

        return redirect()
            ->route('master.branches.index')
            ->with('success', 'Site berhasil ditambahkan.');
    }

    /**
     * Form Edit Site
     */
    public function edit(Branch $branch): View
    {
        $companies = Company::active()
            ->orderBy('name')
            ->get();

        return view('master.Branches.edit', compact(
            'branch',
            'companies'
        ));
    }

    /**
     * Update Site
     */
    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'code'       => ['required', 'string', 'max:50', 'unique:branches,code,' . $branch->id],
            'name'       => ['required', 'string', 'max:255'],
            'type'       => ['nullable', 'string', 'max:50'],
            'email'      => ['nullable', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'address'    => ['nullable', 'string'],
            'status'     => ['required', 'in:active,inactive,closed'],
        ]);

        $branch->update($validated);

        return redirect()
            ->route('master.branches.index')
            ->with('success', 'Site berhasil diperbarui.');
    }

    /**
     * Hapus Site
     */
    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();

        return redirect()
            ->route('master.branches.index')
            ->with('success', 'Site berhasil dihapus.');
    }
}