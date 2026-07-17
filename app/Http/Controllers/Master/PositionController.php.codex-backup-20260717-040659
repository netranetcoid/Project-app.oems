<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(): View
    {
        $companyId = (int) session('company_id');

        return view('master.positions.index', [
            'positions' => Position::forCompany($companyId)
                ->with(['division', 'parent'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(30),
            'divisions' => Division::forCompany($companyId)->active()->orderBy('name')->get(),
            'parents' => Position::forCompany($companyId)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = (int) session('company_id');
        Position::create($this->validated($request, $companyId) + ['company_id' => $companyId]);

        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $companyId = (int) session('company_id');
        $this->ensureCompany($position, $companyId);
        $position->update($this->validated($request, $companyId, $position));

        return back()->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $companyId = (int) session('company_id');
        $this->ensureCompany($position, $companyId);

        if ($position->children()->exists() || Employee::query()->where('position_id', $position->id)->exists()) {
            return back()->withErrors(['position' => 'Jabatan masih dipakai atau memiliki jabatan turunan.']);
        }

        $position->delete();
        return back()->with('success', 'Jabatan berhasil dihapus.');
    }

    private function validated(Request $request, int $companyId, ?Position $position = null): array
    {
        $data = $request->validate([
            'division_id' => ['nullable', Rule::exists('divisions', 'id')->where('company_id', $companyId)],
            'parent_id' => ['nullable', Rule::exists('positions', 'id')->where('company_id', $companyId), Rule::notIn([$position?->id])],
            'code' => ['required', 'string', 'max:50', Rule::unique('positions', 'code')->where('company_id', $companyId)->ignore($position?->id)],
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1', 'max:99'],
            'grade' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', Rule::in(['staff', 'leader', 'supervisor', 'manager', 'director', 'owner'])],
            'default_basic_salary' => ['nullable', 'numeric', 'min:0'],
            'default_allowance' => ['nullable', 'numeric', 'min:0'],
            'default_kpi_incentive_max' => ['nullable', 'numeric', 'min:0'],
            'is_approver' => ['nullable', 'boolean'],
            'is_management' => ['nullable', 'boolean'],
            'is_field_worker' => ['nullable', 'boolean'],
            'is_kpi_enabled' => ['nullable', 'boolean'],
            'is_payroll_enabled' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        foreach (['is_approver', 'is_management', 'is_field_worker', 'is_kpi_enabled', 'is_payroll_enabled'] as $boolean) {
            $data[$boolean] = $request->boolean($boolean);
        }

        return $data;
    }

    private function ensureCompany(Position $position, int $companyId): void
    {
        abort_unless((int) $position->company_id === $companyId, 403);
    }
}
