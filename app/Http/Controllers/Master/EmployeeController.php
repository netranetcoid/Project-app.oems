<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\EmployeeKpiAssessment;
use App\Models\Attendance;
use App\Models\PayrollSlip;
use App\Models\Position;
use App\Services\Employee\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {}

    /**
     * ==========================================================
     * INDEX
     * ==========================================================
     */

    public function index(): View
    {
        $companyId = session('company_id');

        $employees = Employee::query()
    ->with([
        'branch.parent',
        'division',
        'position',
        'supervisor',
        'manager',
    ])
    ->forCompany($companyId)
    ->orderBy('name')
    ->paginate(20);

        return view('master.employees.index', compact(
            'employees'
        ));
    }

    /**
     * ==========================================================
     * CREATE
     * ==========================================================
     */

    public function create(): View
    {
        $companyId = session('company_id');

        $branches = Branch::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $divisions = Division::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $positions = Position::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $supervisors = Employee::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $managers = Employee::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

$roles = Role::query()
    ->orderBy('name')
    ->get();

        return view(
            'master.employees.create',
            compact(
                'branches',
                'divisions',
                'positions',
                'supervisors',
                'managers',
                'roles'
            )
        );
    }

    /**
     * ==========================================================
     * STORE
     * ==========================================================
     */

    public function store(
        StoreEmployeeRequest $request
    ): RedirectResponse {

        $this->employeeService->store(
            $request->validated()
        );

        return redirect()
            ->route('employees.index')
            ->with(
                'success',
                'Pegawai berhasil ditambahkan.'
            );
    }
    /**
 * ==========================================================
 * SHOW
 * ==========================================================
 */

public function show(
    Employee $employee
): View {

    abort_if(
        $employee->company_id != session('company_id'),
        403
    );

    $employee->load([

        'company',

        'branch.parent',

        'division',

        'position',

        'supervisor',

        'manager',

        'user',

    ]);

    // Semua ringkasan di halaman pegawai wajib dibatasi company aktif. Ini
    // mencegah HR lintas branch/site membaca data payroll atau absensi company lain.
    $companyId = (int) session('company_id');
    $contracts = EmployeeContract::forCompany($companyId)
        ->with('contractType')->where('employee_id', $employee->id)
        ->latest('start_date')->limit(10)->get();
    $attendances = Attendance::query()->with('shift')
        ->where('company_id', $companyId)->where('employee_id', $employee->id)
        ->latest('date')->latest('id')->limit(31)->get();
    $kpiAssessments = EmployeeKpiAssessment::forCompany($companyId)
        ->where('employee_id', $employee->id)->latest('id')->limit(12)->get();
    $payrollSlips = PayrollSlip::forCompany($companyId)->with('period')
        ->where('employee_id', $employee->id)->latest('id')->limit(12)->get();

    return view(
        'master.employees.show',
        compact(
            'employee', 'contracts', 'attendances', 'kpiAssessments', 'payrollSlips'
        )
    );
}

    /**
     * ==========================================================
     * EDIT
     * ==========================================================
     */

    public function edit(
        Employee $employee
    ): View {

        abort_if(
            $employee->company_id != session('company_id'),
            403
        );

        $companyId = session('company_id');

        $employee->load([
    'branch',
    'division',
    'position',
    'supervisor',
    'manager',
]);

        $branches = Branch::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $divisions = Division::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $positions = Position::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $supervisors = Employee::forCompany($companyId)
            ->where('id', '!=', $employee->id)
            ->active()
            ->orderBy('name')
            ->get();

        $managers = Employee::forCompany($companyId)
            ->where('id', '!=', $employee->id)
            ->active()
            ->orderBy('name')
            ->get();

        $roles = Role::orderBy('name')->get();

        return view(
            'master.employees.edit',
            compact(
                'employee',
                'branches',
                'divisions',
                'positions',
                'supervisors',
                'managers',
                'roles'
            )
        );
    }

    /**
     * ==========================================================
     * UPDATE
     * ==========================================================
     */

    public function update(
        UpdateEmployeeRequest $request,
        Employee $employee
    ): RedirectResponse {

        abort_if(
            $employee->company_id != session('company_id'),
            403
        );

        $this->employeeService->update(
            $employee,
            $request->validated()
        );

        return redirect()
            ->route('employees.index')
            ->with(
                'success',
                'Pegawai berhasil diperbarui.'
            );
    }

    /**
     * ==========================================================
     * DELETE
     * ==========================================================
     */

    public function destroy(
        Employee $employee
    ): RedirectResponse {

        abort_if(
            $employee->company_id != session('company_id'),
            403
        );

        $this->employeeService->delete(
            $employee
        );

        return redirect()
            ->route('employees.index')
            ->with(
                'success',
                'Pegawai berhasil dihapus.'
            );
    }
}
