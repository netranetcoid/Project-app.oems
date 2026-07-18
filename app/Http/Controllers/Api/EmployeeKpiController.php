<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeKpiAssessment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeKpiController extends Controller
{
    /** Hanya hasil KPI milik karyawan login; HR tetap mengelola detail penilaian di AppOEMS. */
    public function __invoke(Request $request): JsonResponse
    {
        $employee = $request->user()?->employee;
        abort_unless($employee && (int) $employee->company_id === (int) $request->user()->company_id, 403);

        $items = EmployeeKpiAssessment::query()
            ->forCompany((int) $employee->company_id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'published'])
            ->latest('period_year')
            ->latest('period_month')
            ->limit(12)
            ->get()
            ->map(fn (EmployeeKpiAssessment $assessment): array => [
                'period_month' => $assessment->period_month,
                'period_year' => $assessment->period_year,
                'total_score' => (float) $assessment->total_score,
                'grade' => $assessment->grade,
                'bonus_amount' => (float) $assessment->bonus_amount,
                'payout_date' => $assessment->payout_date?->toDateString(),
                'status' => $assessment->status,
            ])->values();

        return response()->json(['data' => ['items' => $items]]);
    }
}
