<?php

namespace App\Services\Document;

use App\Models\Company;
use App\Models\CompanyDocument;

/** Replaces safe placeholders when an owner previews/prints a template. */
class CompanyDocumentRenderService
{
    public function render(CompanyDocument $document, Company $company, array $values = []): string
    {
        $defaults = [
            'company_name' => $company->name,
            'company_legal_name' => $company->legal_name ?: $company->name,
            'company_address' => $company->address ?: '-',
            'company_phone' => $company->phone ?: '-',
            'company_email' => $company->email ?: '-',
            'company_npwp' => $company->npwp ?: '-',
            'company_nib' => $company->nib ?: '-',
            'document_date' => now()->translatedFormat('d F Y'),
            'document_no' => '________________',
            'subject' => $document->subject ?: $document->name,
        ];

        $tokens = array_merge($defaults, array_filter($values, fn ($value) => $value !== null && $value !== ''));
        $body = $document->body;

        foreach ($tokens as $key => $value) {
            // Escape values, but not the master HTML. Only HR/owner with
            // permission can edit master HTML from the protected UI.
            $body = str_replace('[[' . $key . ']]', e((string) $value), $body);
        }

        return $body;
    }
}
