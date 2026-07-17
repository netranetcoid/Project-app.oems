<?php

namespace App\Http\Requests\Kpi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreKpiStandardRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $companyId = (int) session('company_id');

        return [
            'position_id' => ['required', Rule::exists('positions', 'id')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:150'],
            'bonus_maximum' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.aspect_id' => ['required', 'distinct', Rule::exists('kpi_aspects', 'id')->where('company_id', $companyId)],
            'items.*.guideline' => ['nullable', 'string'],
            'items.*.weight' => ['required', 'numeric', 'gt:0', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $totalWeight = collect($this->input('items', []))->sum(fn (array $item) => (float) ($item['weight'] ?? 0));

            if (round($totalWeight, 2) !== 100.0) {
                $validator->errors()->add('items', 'Total bobot KPI harus tepat 100%.');
            }
        }];
    }
}
