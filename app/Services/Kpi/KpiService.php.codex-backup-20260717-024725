<?php

namespace App\Services\Kpi;

use App\Models\Employee;
use App\Models\EmployeeKpiAssessment;
use App\Models\KpiAspect;
use App\Models\KpiStandard;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KpiService
{
    public function createAspect(array $data): KpiAspect
    {
        return KpiAspect::create([...$data, 'company_id' => session('company_id'), 'is_active' => (bool) ($data['is_active'] ?? true)]);
    }

    public function createStandard(array $data): KpiStandard
    {
        return DB::transaction(function () use ($data) {
            $companyId = (int) session('company_id');
            $standard = KpiStandard::create([
                'company_id' => $companyId,
                'position_id' => $data['position_id'],
                'name' => $data['name'],
                'bonus_maximum' => $data['bonus_maximum'],
                'is_active' => (bool) ($data['is_active'] ?? true),
                'notes' => $data['notes'] ?? null,
            ]);

            $aspects = KpiAspect::forCompany($companyId)->whereIn('id', collect($data['items'])->pluck('aspect_id'))->get()->keyBy('id');

            foreach ($data['items'] as $order => $item) {
                $aspect = $aspects->get($item['aspect_id']);
                $standard->items()->create([
                    'kpi_aspect_id' => $aspect->id,
                    'aspect_name' => $aspect->name,
                    'guideline' => $item['guideline'] ?? $aspect->description,
                    'weight' => $item['weight'],
                    'sort_order' => $order + 1,
                ]);
            }

            return $standard;
        });
    }

    public function createAssessment(array $data, int $assessorId): EmployeeKpiAssessment
    {
        return DB::transaction(function () use ($data, $assessorId) {
            $companyId = (int) session('company_id');
            $employee = Employee::forCompany($companyId)->findOrFail($data['employee_id']);
            $standard = KpiStandard::forCompany($companyId)->active()->with('items')->findOrFail($data['kpi_standard_id']);

            if ((int) $employee->position_id !== (int) $standard->position_id) {
                throw ValidationException::withMessages(['kpi_standard_id' => 'Standar KPI harus sesuai dengan jabatan pegawai.']);
            }

            if ($standard->items->isEmpty()) {
                throw ValidationException::withMessages(['kpi_standard_id' => 'Standar KPI belum memiliki aspek penilaian.']);
            }

            $alreadyExists = EmployeeKpiAssessment::forCompany($companyId)
                ->where('employee_id', $employee->id)
                ->where('period_month', $data['period_month'])
                ->where('period_year', $data['period_year'])
                ->exists();

            if ($alreadyExists) {
                throw ValidationException::withMessages(['period_month' => 'KPI pegawai untuk periode ini sudah ada.']);
            }

            $total = 0;
            $items = [];

            foreach ($standard->items as $item) {
                if (!array_key_exists($item->id, $data['scores'])) {
                    throw ValidationException::withMessages(['scores' => 'Semua aspek KPI harus diberi nilai.']);
                }

                $score = (float) $data['scores'][$item->id];
                $weightedScore = round(($score * (float) $item->weight) / 100, 2);
                $total += $weightedScore;
                $items[] = ['item' => $item, 'score' => $score, 'weighted_score' => $weightedScore];
            }

            $total = round($total, 2);
            $employeeLimit = (float) ($employee->kpi_incentive_max ?? 0);
            $bonusMaximum = $employeeLimit > 0
                ? min($employeeLimit, (float) $standard->bonus_maximum)
                : (float) $standard->bonus_maximum;
            $periodDate = \Carbon\Carbon::create((int)$data['period_year'], (int)$data['period_month'], 1);
            $assessment = EmployeeKpiAssessment::create([
                'company_id' => $companyId,
                'employee_id' => $employee->id,
                'position_id' => $employee->position_id,
                'kpi_standard_id' => $standard->id,
                'period_month' => $data['period_month'],
                'period_year' => $data['period_year'],
                'assessor_id' => $assessorId,
                'status' => 'submitted',
                'total_score' => $total,
                'grade' => $this->gradeFor($total),
                'bonus_maximum' => $bonusMaximum,
                'bonus_amount' => round(($total / 100) * $bonusMaximum, 2),
                'payout_date' => $periodDate->addMonthNoOverflow()->day(15)->toDateString(),
                'source_summary' => $data['source_summary'] ?? ['calculation' => 'manual_framework', 'ticket_task_integration' => 'pending_source_module'],
                'notes' => $data['notes'] ?? null,
                'submitted_at' => now(),
            ]);

            foreach ($items as $item) {
                $assessment->items()->create([
                    'kpi_aspect_id' => $item['item']->kpi_aspect_id,
                    'aspect_name' => $item['item']->aspect_name,
                    'guideline' => $item['item']->guideline,
                    'weight' => $item['item']->weight,
                    'score' => $item['score'],
                    'weighted_score' => $item['weighted_score'],
                ]);
            }

            return $assessment;
        });
    }

    public function approve(EmployeeKpiAssessment $assessment, int $approverId, ?string $reviewNote = null): void
    {
        if ($assessment->status !== 'submitted') {
            throw ValidationException::withMessages(['assessment' => 'Hanya KPI berstatus diajukan yang dapat disetujui.']);
        }

        $assessment->update(['status' => 'approved', 'approved_by' => $approverId, 'approved_at' => now(), 'review_note' => $reviewNote]);
    }

    public function reject(EmployeeKpiAssessment $assessment, int $approverId, string $reviewNote): void
    {
        if ($assessment->status !== 'submitted') {
            throw ValidationException::withMessages(['assessment' => 'Hanya KPI berstatus diajukan yang dapat ditolak.']);
        }

        $assessment->update(['status' => 'rejected', 'approved_by' => $approverId, 'review_note' => $reviewNote]);
    }

    private function gradeFor(float $score): string
    {
        return match (true) { $score >= 90 => 'A', $score >= 80 => 'B', $score >= 70 => 'C', default => 'D' };
    }
}
