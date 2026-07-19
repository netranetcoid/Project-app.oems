<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Secure document vault for employee administration.
 * Never use public/storage for files managed by this controller.
 */
class EmployeeDocumentController extends Controller
{
    public function index(Employee $employee): View
    {
        $this->ensureCompany($employee);

        return view('master.employees.documents.index', [
            'employee' => $employee,
            'documents' => $employee->documents()->with(['uploader', 'verifier'])->latest()->get(),
            'types' => $this->types(),
        ]);
    }

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->ensureCompany($employee);
        $data = $request->validate([
            'document_type' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1500'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $path = sprintf(
            'employee-documents/%d/%d/%s.%s',
            $employee->company_id,
            $employee->id,
            Str::uuid(),
            $extension
        );

        // The local disk is intentionally private. Download/view goes through
        // the protected route below after permission and company checks.
        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        EmployeeDocument::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'document_type' => $data['document_type'],
            'title' => $data['title'] ?: ($this->types()[$data['document_type']] ?? 'Dokumen Pegawai'),
            'original_name' => $file->getClientOriginalName(),
            'disk' => 'local',
            'file_path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize(),
            'sha256' => hash_file('sha256', $file->getRealPath()),
            'status' => 'uploaded',
            'is_required' => $request->boolean('is_required'),
            'is_sensitive' => true,
            'expires_at' => $data['expires_at'] ?? null,
            'notes' => $data['notes'] ?? null,
            'uploaded_by' => $request->user()?->id,
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah dan menunggu verifikasi HR.');
    }

    public function updateStatus(Request $request, Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        $this->ensureDocument($employee, $document);
        $data = $request->validate([
            'status' => ['required', 'in:uploaded,verified,rejected,expired'],
            'notes' => ['nullable', 'string', 'max:1500'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $data['verified_by'] = $data['status'] === 'verified' ? $request->user()?->id : null;
        $data['verified_at'] = $data['status'] === 'verified' ? now() : null;
        $document->update($data);

        return back()->with('success', 'Status dokumen berhasil diperbarui.');
    }

    public function download(Employee $employee, EmployeeDocument $document)
    {
        $this->ensureDocument($employee, $document);
        abort_unless(Storage::disk($document->disk)->exists($document->file_path), 404, 'File dokumen tidak ditemukan.');

        return Storage::disk($document->disk)->download($document->file_path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        $this->ensureDocument($employee, $document);
        Storage::disk($document->disk)->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen pegawai berhasil dihapus.');
    }

    private function ensureCompany(Employee $employee): void
    {
        abort_if($employee->company_id !== (int) session('company_id'), 403);
    }

    private function ensureDocument(Employee $employee, EmployeeDocument $document): void
    {
        $this->ensureCompany($employee);
        abort_if($document->company_id !== $employee->company_id || $document->employee_id !== $employee->id, 404);
    }

    private function types(): array
    {
        return [
            'ktp' => 'KTP', 'kk' => 'Kartu Keluarga', 'npwp' => 'NPWP',
            'bpjs_kesehatan' => 'Kartu BPJS Kesehatan',
            'bpjs_ketenagakerjaan' => 'Kartu BPJS Ketenagakerjaan',
            'bank_account' => 'Bukti Rekening Payroll', 'photo' => 'Foto Pegawai',
            'signature' => 'Tanda Tangan', 'employment_contract' => 'Kontrak Ditandatangani',
            'cv' => 'CV', 'diploma' => 'Ijazah', 'certificate' => 'Sertifikat Kompetensi',
            'sim' => 'SIM', 'other' => 'Dokumen Lain',
        ];
    }
}
