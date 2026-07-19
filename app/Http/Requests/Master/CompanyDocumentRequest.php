<?php

namespace App\Http\Requests\Master;

use App\Models\CompanyDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $document = $this->route('companyDocument');
        $documentId = $document instanceof CompanyDocument ? $document->id : null;

        return [
            'code' => [
                'required', 'string', 'max:80', 'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('company_documents', 'code')
                    ->where('company_id', session('company_id'))
                    ->ignore($documentId),
            ],
            'category' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1500'],
            'body' => ['required', 'string', 'max:50000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
