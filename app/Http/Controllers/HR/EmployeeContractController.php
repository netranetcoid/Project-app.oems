<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreEmployeeContractRequest;
use App\Http\Requests\HR\UpdateEmployeeContractRequest;
use App\Models\ContractType;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Services\Contract\EmployeeContractService;
use App\Services\Contract\ContractTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeContractController extends Controller
{
    public function __construct(
        protected EmployeeContractService $service
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $contracts = EmployeeContract::query()
            ->with([
                'employee',
                'contractType',
                'creator',
                'approver',
            ])
            ->forCompany(session('company_id'))
            ->latest()
            ->paginate(20);

        return view('hr.contracts.index', compact('contracts'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $companyId = session('company_id');

        $employees = Employee::query()
            ->forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $contractTypes = ContractType::query()
            ->forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        return view(
            'hr.contracts.create',
            compact(
                'employees',
                'contractTypes'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreEmployeeContractRequest $request
    ): RedirectResponse {

        $this->service->store(
            $request->validated()
        );

        return redirect()
            ->route('hr.contracts.index')
            ->with(
                'success',
                'Kontrak berhasil dibuat.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(
        EmployeeContract $contract
    ): View {

        abort_if(
            $contract->company_id != session('company_id'),
            403
        );

        $contract->load([
            'employee',
            'contractType',
            'creator',
            'approver',
        ]);

        return view(
            'hr.contracts.show',
            compact('contract')
        );
    }

    /*
|--------------------------------------------------------------------------
| EXTEND
|--------------------------------------------------------------------------
*/

public function extend(
    EmployeeContract $contract
): View {

    abort_if(
        $contract->company_id != session('company_id'),
        403
    );

    $companyId = session('company_id');

    $employees = Employee::query()
        ->forCompany($companyId)
        ->active()
        ->orderBy('name')
        ->get();

    $contractTypes = ContractType::query()
        ->forCompany($companyId)
        ->active()
        ->orderBy('name')
        ->get();

    return view(
        'hr.contracts.extend',
        compact(
            'contract',
            'employees',
            'contractTypes'
        )
    );

}

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        EmployeeContract $contract
    ): View {

        abort_if(
            $contract->company_id != session('company_id'),
            403
        );

        $companyId = session('company_id');

        $employees = Employee::query()
            ->forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $contractTypes = ContractType::query()
            ->forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();

        return view(
            'hr.contracts.edit',
            compact(
                'contract',
                'employees',
                'contractTypes'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateEmployeeContractRequest $request,
        EmployeeContract $contract
    ): RedirectResponse {

        abort_if(
            $contract->company_id != session('company_id'),
            403
        );

        $this->service->update(
            $contract,
            $request->validated()
        );

        return redirect()
            ->route('hr.contracts.index')
            ->with(
                'success',
                'Kontrak berhasil diperbarui.'
            );
    }

    /*
|--------------------------------------------------------------------------
| STORE EXTENSION
|--------------------------------------------------------------------------
*/

public function storeExtension(
    StoreEmployeeContractRequest $request,
    EmployeeContract $contract
): RedirectResponse {

    abort_if(
        $contract->company_id != session('company_id'),
        403
    );

    $this->service->extend(
        $contract,
        $request->validated()
    );

    return redirect()
        ->route('hr.contracts.index')
        ->with(
            'success',
            'Kontrak berhasil diperpanjang.'
        );

}

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(
        EmployeeContract $contract
    ): RedirectResponse {

        abort_if(
            $contract->company_id != session('company_id'),
            403
        );

        $this->service->delete($contract);

        return redirect()
            ->route('hr.contracts.index')
            ->with(
                'success',
                'Kontrak berhasil dihapus.'
            );
    }
    public function print(EmployeeContract $contract)
{
    abort_if(
        $contract->company_id != session('company_id'),
        403
    );

    $contract->load([
        'employee',
        'contractType',
    ]);

    $addendum = app(ContractTemplateService::class)
        ->renderAddendum($contract);

    return view(
        'hr.contracts.print',
        compact('contract', 'addendum')
    );
}

    public function pdf(EmployeeContract $contract)
    {
        abort_if($contract->company_id != session('company_id'), 403);

        return app(\App\Services\Contract\ContractPdfService::class)
            ->stream($contract);
    }

    public function approve(EmployeeContract $contract): RedirectResponse
    {
        abort_if($contract->company_id != session('company_id'), 403);

        $this->service->approve($contract);

        return redirect()
            ->route('hr.contracts.show', $contract)
            ->with('success', 'Kontrak disetujui dan status kerja pegawai diperbarui.');
    }
}
