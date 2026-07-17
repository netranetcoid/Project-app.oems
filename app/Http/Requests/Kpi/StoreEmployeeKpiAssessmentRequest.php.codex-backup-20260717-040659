<?php

namespace App\Http\Requests\Kpi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeKpiAssessmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $companyId = (int) session('company_id');

        return [
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'kpi_standard_id' => ['required', Rule::exists('kpi_standards', 'id')->where('company_id', $companyId)],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'between:2020,2100'],
            'notes' => ['nullable', 'string'],
            'scores' => ['required', 'array', 'min:1'],
            'scores.*' => ['required', 'numeric', 'between:0,100'],
            'source_summary' => ['nullable', 'array'],
        ];
    }
}
