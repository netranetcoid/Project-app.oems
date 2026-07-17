<?php

namespace App\Http\Requests\Master\ContractType;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'code' => 'required|string|max:30',

            'name' => 'required|string|max:100',

            'default_duration_month' => 'nullable|integer|min:1|max:60',

            'is_probation' => 'nullable|boolean',

            'is_permanent' => 'nullable|boolean',

            'color' => 'required|string|max:30',

            'description' => 'nullable|string',

            'template_key' => 'nullable|string|max:80|regex:/^[a-z0-9_-]+$/',

            'legal_basis' => 'nullable|string|max:10000',

            'template_body' => 'nullable|string|max:30000',

            'is_active' => 'nullable|boolean',

        ];
    }

    public function attributes(): array
    {
        return [

            'code' => 'Kode',

            'name' => 'Nama Jenis Kontrak',

            'default_duration_month' => 'Durasi',

            'is_probation' => 'Probation',

            'is_permanent' => 'Permanent',

            'color' => 'Warna',

            'description' => 'Deskripsi',

        ];
    }

    public function messages(): array
    {
        return [

            'required' => ':attribute wajib diisi.',

            'integer' => ':attribute harus berupa angka.',

            'max' => ':attribute melebihi batas.',

            'min' => ':attribute tidak valid.',

        ];
    }
}
