<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\CompanyDocumentRequest;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Services\Document\CompanyDocumentReference;
use App\Services\Document\CompanyDocumentRenderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = (int) session('company_id');
        $query = CompanyDocument::query()->forCompany($companyId)->orderBy('category')->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%"));
        }

        return view('master.company-documents.index', [
            'documents' => $query->paginate(20)->withQueryString(),
            'categories' => $this->categories(),
        ]);
    }

    public function create(CompanyDocumentReference $references): View
    {
        return view('master.company-documents.create', [
            'references' => $references->all(),
            'categories' => $this->categories(),
        ]);
    }

    public function store(CompanyDocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = session('company_id');
        $data['is_active'] = $request->boolean('is_active');
        $data['template_version'] = 1;

        CompanyDocument::create($data);

        return redirect()->route('master.company-documents.index')
            ->with('success', 'Master dokumen berhasil disimpan.');
    }

    public function show(CompanyDocument $companyDocument): View
    {
        $this->ensureCompany($companyDocument);

        return view('master.company-documents.show', compact('companyDocument'));
    }

    public function edit(CompanyDocument $companyDocument, CompanyDocumentReference $references): View
    {
        $this->ensureCompany($companyDocument);

        return view('master.company-documents.edit', [
            'companyDocument' => $companyDocument,
            'references' => $references->all(),
            'categories' => $this->categories(),
        ]);
    }

    public function update(CompanyDocumentRequest $request, CompanyDocument $companyDocument): RedirectResponse
    {
        $this->ensureCompany($companyDocument);
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        // Versioning tells HR that the currently editable master changed.
        if (trim($companyDocument->body) !== trim($data['body'])) {
            $data['template_version'] = $companyDocument->template_version + 1;
        }

        $companyDocument->update($data);

        return redirect()->route('master.company-documents.index')
            ->with('success', 'Master dokumen berhasil diperbarui.');
    }

    public function destroy(CompanyDocument $companyDocument): RedirectResponse
    {
        $this->ensureCompany($companyDocument);

        if ($companyDocument->is_system) {
            return back()->with('error', 'Template bawaan tidak dihapus. Nonaktifkan bila belum digunakan.');
        }

        $companyDocument->delete();

        return redirect()->route('master.company-documents.index')
            ->with('success', 'Master dokumen berhasil dihapus.');
    }

    public function print(Request $request, CompanyDocument $companyDocument, CompanyDocumentRenderService $renderer): View
    {
        $this->ensureCompany($companyDocument);
        $company = Company::findOrFail(session('company_id'));

        $values = $request->only([
            'document_no', 'document_date', 'subject', 'recipient_name',
            'sender_name', 'employee_name', 'position_name', 'division_name',
            'destination', 'effective_date', 'end_date', 'scope', 'instruction',
            'notes', 'partner_name', 'owner_name', 'division_name', 'activity_name',
            'item_name', 'quantity', 'item_condition', 'pic_name',
        ]);

        return view('master.company-documents.print', [
            'companyDocument' => $companyDocument,
            'company' => $company,
            'renderedBody' => $renderer->render($companyDocument, $company, $values),
            'values' => $values,
        ]);
    }

    private function ensureCompany(CompanyDocument $companyDocument): void
    {
        abort_if($companyDocument->company_id !== (int) session('company_id'), 403);
    }

    private function categories(): array
    {
        return [
            'sop' => 'SOP', 'hr' => 'Human Resource', 'legal' => 'Legal / Kerja Sama',
            'internal' => 'Internal', 'letter' => 'Surat Resmi',
            'operational' => 'Operasional', 'bpjs' => 'BPJS / Kepatuhan',
        ];
    }
}
