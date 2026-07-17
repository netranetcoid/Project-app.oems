<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayrollSlip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayrollController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employee = $this->employee($request);
        $slips = PayrollSlip::query()->where('employee_id', $employee->id)
            ->where('company_id', $employee->company_id)->where('status', 'published')
            ->with('period')->latest('published_at')->get()
            ->map(fn (PayrollSlip $slip) => [
                'id' => $slip->id,
                'period' => sprintf('%02d/%04d', $slip->period->period_month, $slip->period->period_year),
                'net_pay' => (float) $slip->net_pay,
                'kpi_bonus' => (float) $slip->kpi_bonus,
                'kpi_payment_date' => $slip->kpi_payment_date?->toDateString(),
                'status' => $slip->status,
            ]);
        return response()->json(['data' => $slips]);
    }

    public function show(Request $request, PayrollSlip $slip): JsonResponse
    {
        $employee = $this->employee($request);
        abort_unless((int) $slip->employee_id === (int) $employee->id && $slip->status === 'published', 404);
        return response()->json(['data' => $slip->load(['period', 'items'])]);
    }

    public function payslip(Request $request, PayrollSlip $slip): Response
    {
        $employee = $this->employee($request);
        abort_unless((int) $slip->employee_id === (int) $employee->id && $slip->status === 'published', 404);
        $html = view('hr.payroll.payslip', ['slip' => $slip->load(['period', 'items'])])->render();
        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function employee(Request $request)
    {
        $employee = $request->user()?->employee;
        abort_unless($employee && (int) $employee->company_id === (int) $request->user()->company_id, 403, 'Akun tidak terhubung ke data karyawan.');
        return $employee;
    }
}
