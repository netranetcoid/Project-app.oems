<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Branch;
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
                ->with(['division.branch.parent', 'branch.parent', 'parent'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(30),
            'divisions' => Division::forCompany($companyId)->active()->orderBy('name')->get(),
            'branches' => Branch::forCompany($companyId)->active()->with('parent')->orderBy('name')->get(),
            'parents' => Position::forCompany($companyId)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = (int) session('company_id');
        $data = $this->validated($request, $companyId);
        $data['company_id'] = $companyId;
        $data['code'] = 'POS-' . str_pad((string) (Position::forCompany($companyId)->count() + 1), 4, '0', STR_PAD_LEFT);
        Position::create($data);

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
        // Format input Indonesia, misalnya 1.500.000, dipastikan valid juga
        // saat form dikirim dari browser yang mematikan JavaScript.
        $request->merge(collect(['default_basic_salary', 'default_allowance', 'default_kpi_incentive_max'])
            ->mapWithKeys(fn (string $key) => [$key => $request->filled($key) ? preg_replace('/[^0-9]/', '', (string) $request->input($key)) : null])
            ->all());
        $data = $request->validate([
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'division_id' => ['nullable', Rule::exists('divisions', 'id')->where('company_id', $companyId)],
            'parent_id' => ['nullable', Rule::exists('positions', 'id')->where('company_id', $companyId), Rule::notIn([$position?->id])],
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

        // A division's unit is authoritative; a position cannot silently
        // point to another Branch/Site.
        if (! empty($data['division_id'])) {
            $division = Division::query()->forCompany($companyId)->find($data['division_id']);
            if ($division?->branch_id) {
                if (! empty($data['branch_id']) && (int) $data['branch_id'] !== (int) $division->branch_id) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'branch_id' => 'Branch/Site jabatan harus sama dengan cakupan divisi yang dipilih.',
                    ]);
                }
                $data['branch_id'] = $division->branch_id;
            }
        }

        return $data;
    }

    private function ensureCompany(Position $position, int $companyId): void
    {
        abort_unless((int) $position->company_id === $companyId, 403);
    }
}
