<?php

namespace App\Services\Contract;

use App\Models\EmployeeContract;

/**
 * Renders the frozen employee-contract snapshot safely.
 *
 * Templates use plain tokens such as [[employee_name]]. They are never
 * compiled as Blade/PHP, so HR can edit the text without gaining code
 * execution privileges.
 */
class ContractTemplateService
{
    public function renderAddendum(EmployeeContract $contract): string
    {
        $type = $contract->contractType;
        // Kontrak pegawai menyimpan snapshot dari Master Kontrak saat dibuat.
        // Master hanya dipakai sebagai fallback untuk data lama yang belum
        // memiliki snapshot; perubahan master tidak mengubah dokumen lama.
        $body = trim((string) ($contract->contract_body ?? ''));

        if ($body === '') {
            $body = trim((string) ($type?->template_body ?? ''));
        }

        if ($body === '') {
            return '';
        }

        $values = [
            'contract_no' => $contract->contract_no,
            'employee_no' => $contract->employee_no,
            'employee_name' => $contract->employee_name,
            'position_name' => $contract->position_name,
            'division_name' => $contract->division_name,
            'branch_name' => $contract->branch_name,
            'start_date' => optional($contract->start_date)->translatedFormat('d F Y'),
            'end_date' => optional($contract->end_date)->translatedFormat('d F Y'),
            'probation_end_date' => optional($contract->probation_end_date)->translatedFormat('d F Y'),
            'duration_month' => $contract->duration_month,
            'basic_salary' => number_format((float) $contract->basic_salary, 0, ',', '.'),
        ];

        foreach ($values as $key => $value) {
            $body = str_replace(
                '[[' . $key . ']]',
                (string) ($value ?? '-'),
                $body
            );
        }

        // HTML selalu di-escape. Paragraf yang diawali PASAL 1 - JUDUL dibuat
        // memakai kelas dokumen cetak HR agar master yang diedit tampil sama
        // rapi di browser print maupun PDF.
        $escaped = e($body);
        return collect(preg_split('/\R{2,}/', $escaped) ?: [$escaped])
            ->filter(fn (string $paragraph): bool => trim($paragraph) !== '')
            ->map(function (string $paragraph): string {
                $lines = preg_split('/\R/', trim($paragraph)) ?: [];
                $heading = trim((string) array_shift($lines));
                $content = implode("\n", $lines);

                if (preg_match('/^(PASAL\s+\d+)(?:\s*[-—]\s*(.+))?$/iu', $heading, $matches)) {
                    $articleTitle = trim((string) ($matches[2] ?? ''));
                    $article = '<div class="article"><h2>' . $matches[1] . '</h2>';
                    if ($articleTitle !== '') {
                        $article .= '<h3>' . $articleTitle . '</h3>';
                    }
                    $article .= '</div>';

                    return $article . ($content !== ''
                        ? '<p class="justify">' . nl2br($content) . '</p>'
                        : '');
                }

                return '<p class="justify">' . nl2br($heading . ($content !== '' ? "\n" . $content : '')) . '</p>';
            })
            ->implode('');
    }
}
