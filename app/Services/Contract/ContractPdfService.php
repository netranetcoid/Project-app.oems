<?php

namespace App\Services\Contract;

use App\Models\EmployeeContract;
use Illuminate\Http\Response;

class ContractPdfService
{
    public function __construct(
        protected ContractTemplateService $templates
    ) {
    }

    public function stream(EmployeeContract $contract): Response
    {
        $contract->loadMissing(['employee', 'contractType']);

        $html = view('hr.contracts.print', [
            'contract' => $contract,
            'addendum' => $this->templates->renderAddendum($contract),
        ])->render();

        // Dompdf is intentionally optional. The browser print route remains
        // usable in a clean install, while production can install dompdf for
        // a server-generated PDF download.
        if (class_exists('Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $contract->contract_no . '.pdf"',
            ]);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-PDF-Notice' => 'Install dompdf for server-side PDF output; browser print is available now.',
        ]);
    }
}
