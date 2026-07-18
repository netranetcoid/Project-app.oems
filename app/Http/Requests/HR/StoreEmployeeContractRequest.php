<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeContractRequest extends FormRequest
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
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where($companyScope)],
            'contract_type_id' => ['required', 'integer', Rule::exists('contract_types', 'id')->where($companyScope)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'duration_month' => ['nullable', 'integer', 'min:1', 'max:60'],
            'letter_no' => ['nullable', 'string', 'max:100'],
            // Isi surat merupakan snapshot dokumen per karyawan. HR boleh
            // menyesuaikan pasal tanpa mengubah template master untuk kontrak lain.
            'contract_body' => ['nullable', 'string', 'max:50000'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
