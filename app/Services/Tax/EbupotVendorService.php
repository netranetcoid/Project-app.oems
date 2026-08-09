<?php

namespace App\Services\Tax;

use App\Models\EbupotVendor;
use App\Models\EbupotVendorRecord;
use App\Models\EbupotVendorSetting;
use Carbon\Carbon;

class EbupotVendorService
{
    public const CHECKS = ['vendor_identity','nitku','tax_object','tax_base','tax_rate','tax_amount','invoice_reference','tax_facility','company_nitku','net_transfer'];

    public function settings(int $companyId): EbupotVendorSetting
    {
        return EbupotVendorSetting::firstOrCreate(['company_id' => $companyId], [
            'wa_initial_template' => "Siang Pak/Bu, izin konfirmasi invoice periode [BULAN]. Mohon konfirmasi kode objek pajak Coretax, NITKU 22 digit, DPP sebelum PPN Rp[DPP], dan apakah ada SKB/fasilitas pajak. Terima kasih.",
            'wa_amount_template' => "Pak/Bu, total invoice Rp[TOTAL], PPh sebesar Rp[PPH]. Jadi transfer bersih Rp[BERSIH] dan bukti potong Rp[PPH], betul ya?",
            'wa_sent_template' => "Pak/Bu, bukti potong untuk invoice [INVOICE] sudah diterbitkan melalui Coretax dan kami kirimkan. Terima kasih.",
            'email_template' => "Dear Tim [VENDOR],\n\nTerlampir Bukti Potong PPh atas invoice [INVOICE] dengan nilai PPh Rp[PPH]. Pembayaran bersih sebesar Rp[BERSIH].\n\nRegards,\nFinance",
        ]);
    }

    public function ensurePeriod(int $companyId, Carbon $period): void
    {
        EbupotVendor::forCompany($companyId)->where('is_active', true)->each(function (EbupotVendor $vendor) use ($companyId, $period) {
            EbupotVendorRecord::firstOrCreate([
                'company_id' => $companyId, 'ebupot_vendor_id' => $vendor->id, 'period' => $period->startOfMonth()->toDateString(),
            ], ['tax_rate' => $vendor->default_tax_rate, 'checklist' => array_fill_keys(self::CHECKS, false)]);
        });
    }

    public function calculate(array $data): array
    {
        $base = (float) ($data['tax_base'] ?? 0); $rate = (float) ($data['tax_rate'] ?? 0);
        $tax = round($base * $rate / 100, 2); $total = (float) ($data['invoice_total'] ?? 0);
        return ['tax_amount' => $tax, 'net_transfer' => max(0, $total - $tax)];
    }

    public function message(string $template, EbupotVendorRecord $record): string
    {
        $replace = ['[BULAN]'=>$record->period->translatedFormat('F Y'),'[VENDOR]'=>$record->vendor->name,'[INVOICE]'=>$record->invoice_number ?: '-',
            '[DPP]'=>number_format((float)$record->tax_base,0,',','.'),'[PPH]'=>number_format((float)$record->tax_amount,0,',','.'),
            '[TOTAL]'=>number_format((float)$record->invoice_total,0,',','.'),'[BERSIH]'=>number_format((float)$record->net_transfer,0,',','.')];
        return strtr($template, $replace);
    }
}
