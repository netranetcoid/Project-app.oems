<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Divisions are explicitly scoped to PT OSM and optionally to a Branch/Site. */
class DivisionController extends Controller
{
    public function index(): View
    {
        $companyId = (int) session('company_id');
        return view('master.Divisions.index', ['divisions' => Division::query()->with(['company', 'branch', 'parent'])
            ->forCompany($companyId)->orderBy('name')->paginate(20)]);
    }

    public function create(): View
    {
        return view('master.Divisions.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = (int) session('company_id');
        $data = $this->validated($request, $companyId);
        $data['company_id'] = $companyId;
        $data['code'] = 'DIV-' . str_pad((string) (Division::query()->forCompany($companyId)->count() + 1), 4, '0', STR_PAD_LEFT);
        Division::create($data);
        return redirect()->route('master.divisions.index')->with('success', 'Divisi berhasil ditambahkan. Kode dibuat otomatis.');
    }

    public function edit(Division $division): View
    {
        $this->ensureCompany($division);
        return view('master.Divisions.edit', $this->formData($division) + compact('division'));
    }

    public function update(Request $request, Division $division): RedirectResponse
    {
        $this->ensureCompany($division);
        $division->update($this->validated($request, (int) $division->company_id, $division));
        return redirect()->route('master.divisions.index')->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy(Division $division): RedirectResponse
    {
        $this->ensureCompany($division);
        if ($division->positions()->exists() || $division->children()->exists()) {
            return back()->withErrors(['division' => 'Divisi masih memiliki jabatan atau divisi turunan.']);
        }
        $division->delete();
        return redirect()->route('master.divisions.index')->with('success', 'Divisi berhasil dihapus.');
    }

    private function formData(?Division $editing = null): array
    {
        $companyId = (int) session('company_id');
        return [
            'company' => Company::query()->findOrFail($companyId),
            'branches' => Branch::query()->forCompany($companyId)->active()->with('parent')->orderBy('name')->get(),
            'parents' => Division::query()->forCompany($companyId)->active()->when($editing, fn ($q) => $q->whereKeyNot($editing->id))->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, int $companyId, ?Division $editing = null): array
    {
        $data = $request->validate([
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'parent_id' => ['nullable', Rule::exists('divisions', 'id')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:255'], 'type' => ['nullable', 'string', 'max:100'],
            'head_name' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
        if ($editing && (int) $data['parent_id'] === (int) $editing->id) {
            throw ValidationException::withMessages(['parent_id' => 'Divisi tidak dapat menjadi induk dirinya sendiri.']);
        }
        return $data;
    }

    private function ensureCompany(Division $division): void
    {
        abort_unless((int) $division->company_id === (int) session('company_id'), 403);
    }
}
