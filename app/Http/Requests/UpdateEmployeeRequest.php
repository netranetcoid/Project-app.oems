<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /** Nominal rupiah boleh diketik dengan titik ribuan, lalu dinormalisasi. */
    protected function prepareForValidation(): void
    {
        $money = ['basic_salary', 'meal_allowance', 'transport_allowance', 'position_allowance'];
        $this->merge(collect($money)->mapWithKeys(fn (string $key) => [$key => $this->filled($key)
            ? preg_replace('/[^0-9]/', '', (string) $this->input($key))
            : null])->all());
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = (int) session('company_id');
        $companyScope = static fn ($query) => $query->where('company_id', $companyId);
        $employee = $this->route('employee');

        $employeeId = is_object($employee)
            ? $employee->id
            : $employee;

        return [

            /*
            |--------------------------------------------------------------------------
            | Organization
            |--------------------------------------------------------------------------
            */

            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where($companyScope)],

            'division_id' => ['nullable', Rule::exists('divisions', 'id')->where($companyScope)],

            'position_id' => ['nullable', Rule::exists('positions', 'id')->where($companyScope)],

            'supervisor_employee_id' => ['nullable', Rule::exists('employees', 'id')->where($companyScope)],

            'manager_employee_id' => ['nullable', Rule::exists('employees', 'id')->where($companyScope)],

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            'employee_no' => [

                'nullable',

                'string',

                'max:100',

                Rule::unique('employees', 'employee_no')
                    ->ignore($employeeId)
                    ->where($companyScope),

            ],

            'name' => 'required|string|max:255',

            'nickname' => 'nullable|string|max:255',

            'email' => [

                'nullable',

                'email',

                'max:255',

                Rule::unique('employees', 'email')
                    ->ignore($employeeId)
                    ->where($companyScope),

            ],

            'phone' => 'nullable|string|max:50',

            'whatsapp' => 'nullable|string|max:50',

            /*
            |--------------------------------------------------------------------------
            | Personal
            |--------------------------------------------------------------------------
            */

            'gender' => 'nullable|in:male,female',

            'birth_place' => 'nullable|string|max:100',

            'birth_date' => 'nullable|date',

            /*
            |--------------------------------------------------------------------------
            | Employment
            |--------------------------------------------------------------------------
            */

            'join_date' => 'required|date',

            'employment_status' => 'required|in:probation,contract,permanent,resign',

            'work_status' => 'required|in:active,inactive',

            /*
            |--------------------------------------------------------------------------
            | Payroll
            |--------------------------------------------------------------------------
            */

            'basic_salary' => 'nullable|numeric|min:0',

            'meal_allowance' => 'nullable|numeric|min:0',

            'transport_allowance' => 'nullable|numeric|min:0',

            'position_allowance' => 'nullable|numeric|min:0',

            /*
            |--------------------------------------------------------------------------
            | Login
            |--------------------------------------------------------------------------
            */

            'create_login' => 'nullable|boolean',

            'role' => ['nullable', Rule::exists('roles', 'name')->where($companyScope)],

            /*
            |--------------------------------------------------------------------------
            | Photo
            |--------------------------------------------------------------------------
            */

            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            /*
            |--------------------------------------------------------------------------
            | Other
            |--------------------------------------------------------------------------
            */

            'notes' => 'nullable|string',

        ];
    }

    public function attributes(): array
    {
        return [

            'employee_no' => 'Nomor Pegawai',

            'name' => 'Nama Pegawai',

            'nickname' => 'Nama Panggilan',

            'email' => 'Email',

            'phone' => 'Nomor HP',

            'whatsapp' => 'WhatsApp',

            'branch_id' => 'Site',

            'division_id' => 'Divisi',

            'position_id' => 'Jabatan',

            'supervisor_employee_id' => 'Supervisor',

            'manager_employee_id' => 'Manager',

            'join_date' => 'Tanggal Masuk',

            'employment_status' => 'Status Pegawai',

            'work_status' => 'Status Kerja',

            'basic_salary' => 'Gaji Pokok',

            'meal_allowance' => 'Uang Makan',

            'transport_allowance' => 'Uang Transport',

            'position_allowance' => 'Tunjangan Jabatan',

            'photo' => 'Foto',

            'role' => 'Role',

            'notes' => 'Catatan',

        ];
    }

    public function messages(): array
    {
        return [

            'required' => ':attribute wajib diisi.',

            'unique' => ':attribute sudah digunakan.',

            'exists' => ':attribute tidak ditemukan.',

            'email' => ':attribute tidak valid.',

            'numeric' => ':attribute harus berupa angka.',

            'image' => ':attribute harus berupa gambar.',

            'mimes' => ':attribute harus berupa JPG, JPEG, PNG atau WEBP.',

            'max' => ':attribute melebihi batas maksimum.',

            'min' => ':attribute kurang dari batas minimum.',

            'in' => ':attribute tidak valid.',

        ];
    }
}
