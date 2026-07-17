<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRequest;
use App\Services\HR\EmployeeRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeRequestController extends Controller
{
    public function __construct(private EmployeeRequestService $service) {}

    public function index(Request $request): JsonResponse
    {
        $employee = $this->employee($request);
        $requests = EmployeeRequest::forCompany((int) $employee->company_id)
            ->where('employee_id', $employee->id)->latest('submitted_at')->limit(50)->get();

        return response()->json(['data' => $requests]);
    }

    public function store(Request $request): JsonResponse
    {
        $employee = $this->employee($request);
        $data = $request->validate([
            'type' => ['required', Rule::in(EmployeeRequestService::TYPES)],
            'start_date' => ['nullable', 'date', 'required_unless:type,cash_advance,receivable,reimbursement'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'requested_amount' => ['nullable', 'numeric', 'min:1', 'required_if:type,cash_advance,receivable,reimbursement'],
            'installment_count' => ['nullable', 'integer', 'min:1', 'max:60'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $path = $request->file('document')?->store(
            'hr-requests/' . $employee->company_id . '/' . $employee->id,
            'local'
        );
        $submitted = $this->service->submit($employee, $data, $path);

        return response()->json(['message' => 'Pengajuan berhasil dikirim.', 'data' => $submitted], 201);
    }

    private function employee(Request $request)
    {
        // Akun developer sengaja tidak dianggap karyawan dan tidak boleh
        // membuat presensi/pengajuan/payroll personal.
        $employee = $request->user()?->employee;
        abort_unless($employee && (int) $employee->company_id === (int) $request->user()->company_id, 403, 'Akun tidak terhubung ke data karyawan.');
        return $employee;
    }
}
