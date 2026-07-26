<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API profil pribadi OvallHR.
 * Seluruh query selalu dimulai dari user login, sehingga pegawai tidak pernah
 * bisa membaca atau mengubah data pegawai lain maupun lintas perusahaan.
 */
class EmployeeSelfServiceProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $employee = $this->employee($request);
        $employee->load(['company:id,name,legal_name', 'branch:id,name', 'division:id,name', 'position:id,name', 'documents']);

        return response()->json(['data' => [
            'employee' => [
                'name' => $employee->name,
                'employee_code' => $employee->employee_no,
                'photo_url' => $employee->photo_url,
                'company' => $employee->company?->legal_name ?: $employee->company?->name,
                'branch' => $employee->branch?->name,
                'division' => $employee->division?->name,
                'position' => $employee->position?->name,
                'employment_status' => $employee->employment_status,
                'work_status' => $employee->work_status,
                'join_date' => optional($employee->join_date)->toDateString(),
                'birth_place' => $employee->birth_place,
                'birth_date' => optional($employee->birth_date)->toDateString(),
                'gender' => $employee->gender,
                'religion' => $employee->religion,
                'marital_status' => $employee->marital_status,
                'identity_type' => $employee->identity_type,
                'identity_number_masked' => $this->mask($employee->identity_number),
                'npwp_masked' => $this->mask($employee->npwp),
                'email' => $employee->email ?: $request->user()?->email,
                'personal_email' => $employee->personal_email,
                'phone' => $employee->phone,
                'whatsapp' => $employee->whatsapp,
                'address' => $employee->full_address,
                'domicile_address' => $employee->domicile_address,
                'emergency_contact_name' => $employee->emergency_contact_name,
                'emergency_contact_relation' => $employee->emergency_contact_relation,
                'emergency_contact_phone' => $this->mask($employee->emergency_contact_phone),
                'bank_name' => $employee->bank_name,
                'bank_account_name' => $employee->bank_account_name,
                'bank_account_masked' => $this->mask($employee->bank_account_no),
                'bpjs_kesehatan_active' => (bool) $employee->is_bpjs_kesehatan_active,
                'bpjs_kesehatan_masked' => $this->mask($employee->bpjs_kesehatan_no),
                'bpjs_ketenagakerjaan_active' => (bool) $employee->is_bpjs_ketenagakerjaan_active,
                'bpjs_ketenagakerjaan_masked' => $this->mask($employee->bpjs_ketenagakerjaan_no),
            ],
            // Hanya metadata yang dikirim; file KTP/KK/NPWP tetap privat dan
            // tidak pernah dibuka lewat API mobile umum.
            'documents' => $employee->documents->map(fn ($document) => [
                'type' => $document->document_type,
                'title' => $document->title,
                'status' => $document->status,
                'expires_at' => optional($document->expires_at)->toDateString(),
                'is_required' => (bool) $document->is_required,
            ])->values(),
        ]]);
    }

    public function updateContact(Request $request): JsonResponse
    {
        $employee = $this->employee($request);
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'domicile_address' => ['nullable', 'string', 'max:2000'],
        ]);

        // Email login, rekening, BPJS, NPWP, dan identitas legal sengaja
        // tidak dapat diubah langsung dari aplikasi; gunakan pengajuan ke HR.
        $employee->update($data);

        return response()->json(['message' => 'Kontak pribadi berhasil diperbarui.']);
    }

    private function employee(Request $request): Employee
    {
        $employee = $request->user()?->employee;
        abort_unless($employee && (int) $employee->company_id === (int) $request->user()->company_id, 403, 'Akun tidak terhubung ke data karyawan.');
        return $employee;
    }

    private function mask(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        $length = mb_strlen($value);
        return $length <= 4 ? str_repeat('â€¢', $length) : str_repeat('â€¢', max(0, $length - 4)) . mb_substr($value, -4);
    }
}
