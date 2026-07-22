<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Manages PT OSM's operational tree: Branches may own multiple Sites. */
class BranchController extends Controller
{
    public function index(): View
    {
        $companyId = (int) session('company_id');
        $branches = Branch::query()->with(['company', 'parent'])->forCompany($companyId)
            ->orderByRaw("CASE WHEN type = 'branch' THEN 0 WHEN type = 'head_office' THEN 1 ELSE 2 END")
            ->orderBy('name')->paginate(20);

        return view('master.Branches.index', compact('branches'));
    }

    public function create(): View
    {
        $company = $this->activeCompany();
        return view('master.Branches.create', [
            'company' => $company,
            'parents' => Branch::query()->forCompany($company->id)->active()
                ->whereIn('type', ['branch', 'head_office'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->activeCompany();
        $data = $this->validated($request, $company->id);
        $data['company_id'] = $company->id;
        $data['code'] = $this->nextCode($company->id, $data['type']);
        Branch::create($data);

        return redirect()->route('master.branches.index')->with('success', 'Branch / Site berhasil ditambahkan. Kode dibuat otomatis.');
    }

    public function edit(Branch $branch): View
    {
        $this->ensureCompany($branch);
        return view('master.Branches.edit', [
            'branch' => $branch,
            'company' => $this->activeCompany(),
            'parents' => Branch::query()->forCompany($branch->company_id)->active()
                ->whereIn('type', ['branch', 'head_office'])->whereKeyNot($branch->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->ensureCompany($branch);
        // Codes are immutable after creation so payroll and external mapping stay stable.
        $branch->update($this->validated($request, (int) $branch->company_id, $branch));
        return redirect()->route('master.branches.index')->with('success', 'Branch / Site berhasil diperbarui.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->ensureCompany($branch);
        if ($branch->sites()->exists()) {
            return back()->withErrors(['branch' => 'Pindahkan atau nonaktifkan Site di bawah Branch ini terlebih dahulu.']);
        }
        $branch->delete();
        return redirect()->route('master.branches.index')->with('success', 'Branch / Site berhasil dihapus.');
    }

    private function validated(Request $request, int $companyId, ?Branch $editing = null): array
    {
        $data = $request->validate([
            'parent_branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['head_office', 'branch', 'site', 'warehouse', 'project'])],
            'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'], 'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'inactive', 'closed'])],
        ]);

        if ($editing && (int) $data['parent_branch_id'] === (int) $editing->id) {
            throw ValidationException::withMessages(['parent_branch_id' => 'Branch tidak dapat menjadi induk dirinya sendiri.']);
        }
        if ($data['type'] === 'site' && empty($data['parent_branch_id'])) {
            throw ValidationException::withMessages(['parent_branch_id' => 'Site wajib berada di bawah Branch atau Head Office.']);
        }
        if ($data['type'] !== 'site') {
            $data['parent_branch_id'] = null;
        }

        return $data;
    }

    private function nextCode(int $companyId, string $type): string
    {
        $prefix = ['head_office' => 'HO', 'branch' => 'BR', 'site' => 'ST', 'warehouse' => 'WH', 'project' => 'PRJ'][$type] ?? 'UNT';
        $sequence = Branch::query()->withTrashed()->forCompany($companyId)->where('code', 'like', $prefix . '-%')->count() + 1;
        return $prefix . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function activeCompany(): Company
    {
        return Company::query()->findOrFail((int) session('company_id'));
    }

    private function ensureCompany(Branch $branch): void
    {
        abort_unless((int) $branch->company_id === (int) session('company_id'), 403);
    }
}
