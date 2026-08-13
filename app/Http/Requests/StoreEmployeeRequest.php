<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    /** Terima 1.500.000 dari UI tanpa bergantung pada JavaScript browser. */
    protected function prepareForValidation(): void
    {
        $money = ['basic_salary', 'fixed_allowance', 'meal_allowance', 'transport_allowance', 'position_allowance'];
        $this->merge(collect($money)->mapWithKeys(fn (string $key) => [$key => $this->filled($key)
            ? \App\Support\RupiahInput::integer($this->input($key))
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

        return [

            // Organization
            // Semua referensi organisasi wajib berasal dari company aktif.
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where($companyScope)],
            'division_id' => ['nullable', Rule::exists('divisions', 'id')->where($companyScope)],
            'position_id' => ['nullable', Rule::exists('positions', 'id')->where($companyScope)],
            'supervisor_employee_id' => ['nullable', Rule::exists('employees', 'id')->where($companyScope)],
            'manager_employee_id' => ['nullable', Rule::exists('employees', 'id')->where($companyScope)],

            // Identity
            'employee_no' => ['nullable', 'string', 'max:100', Rule::unique('employees', 'employee_no')->where($companyScope)],
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('employees', 'email')->where($companyScope)],
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',

            // Personal
            'gender' => 'nullable|in:male,female',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',

            // Alamat lengkap dipakai pada profil, kontrak, dan kesiapan BPJS.
            'address' => 'nullable|string|max:1000',
            'domicile_address' => 'nullable|string|max:1000',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'emergency_contact_address' => 'nullable|string|max:1000',

            // Employment
            'join_date' => 'required|date',
            'employment_status' => 'required|in:probation,contract,permanent,resign',
            'work_status' => 'required|in:active,inactive',

            // Payroll
            'basic_salary' => 'nullable|numeric|min:0',
            'fixed_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'position_allowance' => 'nullable|numeric|min:0',

            // Login
            'create_login' => 'nullable|boolean',
            'role' => ['nullable', Rule::exists('roles', 'name')->where($companyScope)],

            // Photo
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Other
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
            'fixed_allowance' => 'Tunjangan Tetap (Dasar BPJS)',
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
            'email' => ':attribute tidak valid.',
            'exists' => ':attribute tidak ditemukan.',
            'unique' => ':attribute sudah digunakan.',
            'numeric' => ':attribute harus berupa angka.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berupa JPG, JPEG, PNG atau WEBP.',
            'max' => ':attribute melebihi batas maksimum.',
            'min' => ':attribute kurang dari batas minimum.',
            'in' => ':attribute tidak valid.',

        ];
    }
}
