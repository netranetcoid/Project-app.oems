<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = (int) session('company_id');
        $companyScope = static fn ($query) => $query->where('company_id', $companyId);

        return [
            'contract_type_id' => ['required', 'integer', Rule::exists('contract_types', 'id')->where($companyScope)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'duration_month' => ['nullable', 'integer', 'min:1', 'max:60'],
            'letter_no' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:draft,waiting,approved,signed,active,expired,terminated,extended'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
