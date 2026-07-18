<?php

namespace App\Services\Contract;

use App\Models\EmployeeContract;

/**
 * Renders the editable contract addendum safely.
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
        $settings = is_array($type?->settings) ? $type->settings : [];
        // Prioritaskan snapshot yang diedit HR pada kontrak pegawai. Template
        // master hanya dipakai oleh kontrak lama yang belum punya dokumen sendiri.
        $body = trim((string) ($contract->contract_body ?? ''));

        if ($body === '') {
            $body = trim((string) ($type?->template_body ?? ''));
        }

        if ($body === '') {
            $body = (string) ($settings['default_addendum'] ?? '');
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

        // Keep formatting simple and predictable: paragraphs separated by a
        // blank line become a printable paragraph, while HTML is escaped.
        $escaped = e($body);
        return collect(preg_split('/\R{2,}/', $escaped) ?: [$escaped])
            ->map(fn (string $paragraph): string => '<p class="justify">' . nl2br($paragraph) . '</p>')
            ->implode('');
    }
}
