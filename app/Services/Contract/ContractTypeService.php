<?php

namespace App\Services\Contract;

use App\Models\ContractType;
use Illuminate\Support\Facades\DB;

class ContractTypeService
{
    public function __construct(
        protected ContractMasterReference $references,
        protected ContractPrintMasterTemplate $printTemplates
    )
    {
    }
    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(array $data): ContractType
    {
        return DB::transaction(function () use ($data) {

            $companyId = (int) session('company_id');
            if (!$companyId) {
                throw new \RuntimeException('Company aktif belum dipilih.');
            }
            $data['company_id'] = $companyId;
            $this->applyReferenceDefaults($data);

            return ContractType::create($data);

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        ContractType $contractType,
        array $data
    ): ContractType {

        return DB::transaction(function () use (
            $contractType,
            $data
        ) {

            if ((int) $contractType->company_id !== (int) session('company_id')) {
                abort(403, 'Template kontrak bukan bagian dari company aktif.');
            }

            $this->applyReferenceDefaults($data);

            // Browsers may submit CRLF while older saved templates use LF.
            // Normalize first so simply opening/saving never creates a fake
            // version increase; a version changes only for real text edits.
            if (array_key_exists('template_body', $data)) {
                $data['template_body'] = $this->normalizeTemplateBody($data['template_body']);
            }

            // A version gives HR an audit trail: new employee contracts take
            // a snapshot of the new master, while issued contracts remain
            // unchanged and printable as originally approved.
            if (array_key_exists('template_body', $data)
                && $data['template_body'] !== $this->normalizeTemplateBody($contractType->template_body)) {
                $data['template_version'] = ((int) $contractType->template_version) + 1;
            }

            $contractType->update($data);

            return $contractType;

        });

    }

    /** Fill a newly selected official framework without overwriting edits. */
    private function applyReferenceDefaults(array &$data): void
    {
        $reference = $this->references->find($data['template_key'] ?? null);
        if (!$reference) {
            return;
        }

        $data['is_probation'] = (bool) $reference['is_probation'];
        $data['is_permanent'] = (bool) $reference['is_permanent'];
        $data['default_duration_month'] = $data['default_duration_month'] ?: $reference['default_duration_month'];
        // Master baru dimulai dengan 15 pasal yang sama dengan struktur cetak
        // HR. Setelah disimpan, seluruh naskah menjadi milik editor master.
        $data['template_body'] = trim((string) ($data['template_body'] ?? ''))
            ?: $this->printTemplates->bodyFor($data['template_key']);
        $data['legal_basis'] = trim((string) ($data['legal_basis'] ?? '')) ?: $reference['legal_basis'];
    }

    /** Keep document line endings stable across Windows, web, PDF, and VPS. */
    private function normalizeTemplateBody(?string $body): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", (string) $body));
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        ContractType $contractType
    ): void {

        DB::transaction(function () use ($contractType) {

            /*
            |--------------------------------------------------------------------------
            | Cek apakah sudah dipakai kontrak
            |--------------------------------------------------------------------------
            */

            if (
                $contractType->contracts()->exists()
            ) {

                abort(
                    422,
                    'Jenis kontrak sudah digunakan.'
                );

            }

            $contractType->delete();

        });

    }
}
