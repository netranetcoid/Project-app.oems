<?php

namespace App\Http\Requests\Master\Branch;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('branch.create');
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [
            'company_id'               => ['required', 'exists:companies,id'],
            'code'                     => ['required', 'string', 'max:50','unique:branches,code'],
            'name'                     => ['required', 'string', 'max:255'],
            'type'                     => ['nullable', 'string', 'max:50'],
            'email'                    => ['nullable', 'email', 'max:255'],
            'phone'                    => ['nullable', 'string', 'max:50'],
            'mobile_phone'             => ['nullable', 'string', 'max:50'],
            'address'                  => ['nullable', 'string'],
            'province'                 => ['nullable', 'string', 'max:100'],
            'city'                     => ['nullable', 'string', 'max:100'],
            'district'                 => ['nullable', 'string', 'max:100'],
            'village'                  => ['nullable', 'string', 'max:100'],
            'postal_code'              => ['nullable', 'string', 'max:20'],
            'latitude'                 => ['nullable', 'numeric'],
            'longitude'                => ['nullable', 'numeric'],
            'attendance_radius_meter'  => ['nullable', 'integer'],
            'timezone'                 => ['nullable', 'string', 'max:100'],
            'opened_at'                => ['nullable', 'date'],
            'closed_at'                => ['nullable', 'date'],
            'pic_user_id'              => ['nullable', 'exists:users,id'],
            'pic_name'                 => ['nullable', 'string', 'max:255'],
            'pic_phone'                => ['nullable', 'string', 'max:50'],
            'status'                   => ['required', 'in:active,inactive,closed'],
            'notes'                    => ['nullable', 'string'],
        ];
    }

    /**
     * Custom Attribute
     */
    public function attributes(): array
    {
        return [
            'company_id' => 'Perusahaan',
            'code' => 'Kode Site',
            'name' => 'Nama Site',
            'type' => 'Tipe Site',
            'pic_name' => 'PIC Site',
            'pic_user_id' => 'User PIC',
        ];
    }

    /**
     * Custom Message
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'Perusahaan wajib dipilih.',
            'company_id.exists'   => 'Perusahaan tidak ditemukan.',

            'code.required'       => 'Kode Site wajib diisi.',
            'name.required'       => 'Nama Site wajib diisi.',

            'status.required'     => 'Status wajib dipilih.',
        ];
    }
}