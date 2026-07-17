<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'contract_type_id' => [
                'required',
                'exists:contract_types,id'
            ],

            'start_date' => [
                'required',
                'date'
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date'
            ],

            'duration_month' => [
                'nullable',
                'integer',
                'min:1'
            ],

            'letter_no' => [
                'nullable',
                'string',
                'max:100'
            ],

            'status' => [
                'required'
            ],

            'notes' => [
                'nullable',
                'string'
            ],

        ];
    }
}