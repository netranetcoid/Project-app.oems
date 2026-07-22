<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ContractType\StoreContractTypeRequest;
use App\Http\Requests\Master\ContractType\UpdateContractTypeRequest;
use App\Models\ContractType;
use App\Services\Contract\ContractMasterReference;
use App\Services\Contract\ContractTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractTypeController extends Controller
{
    public function __construct(
        protected ContractTypeService $service
    ) {}

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $companyId = session('company_id');

        $query = ContractType::query()
            ->forCompany($companyId);

        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {

                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');

            });

        }

        $contractTypes = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stats = [

            'total' => ContractType::forCompany($companyId)->count(),

            'active' => ContractType::forCompany($companyId)
                ->where('is_active', true)
                ->count(),

            'inactive' => ContractType::forCompany($companyId)
                ->where('is_active', false)
                ->count(),

        ];

        return view('master.contract-types.index', [
            'contractTypes' => $contractTypes,
            'stats' => $stats,
            // Hanya untuk label kategori di daftar. Editor dokumen tetap
            // berada di halaman Master Kontrak agar tidak salah ubah template.
            'templateReferences' => app(ContractMasterReference::class)->all(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view('master.contract-types.create', [
            'templateReferences' => app(ContractMasterReference::class)->all(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreContractTypeRequest $request
    ): RedirectResponse {

        $this->service->store(
            $request->validated()
        );

        return redirect()
            ->route('master.contract-types.index')
            ->with(
                'success',
                'Jenis kontrak berhasil ditambahkan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        ContractType $contractType
    ): View {

        abort_if(
            $contractType->company_id != session('company_id'),
            403
        );

        return view('master.contract-types.edit', [
            'contractType' => $contractType,
            'templateReferences' => app(ContractMasterReference::class)->all(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateContractTypeRequest $request,
        ContractType $contractType
    ): RedirectResponse {

        abort_if(
            $contractType->company_id != session('company_id'),
            403
        );

        $this->service->update(
            $contractType,
            $request->validated()
        );

        return redirect()
            ->route('master.contract-types.index')
            ->with(
                'success',
                'Jenis kontrak berhasil diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ContractType $contractType
    ): RedirectResponse {

        abort_if(
            $contractType->company_id != session('company_id'),
            403
        );

        $this->service->delete(
            $contractType
        );

        return redirect()
            ->route('master.contract-types.index')
            ->with(
                'success',
                'Jenis kontrak berhasil dihapus.'
            );
    }
}
