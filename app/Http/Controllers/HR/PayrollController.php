<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Models\PayrollSlip;
use App\Services\Payroll\PayrollService;
use App\Services\Integration\AppBillIntegrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollController extends Controller
{
    public function __construct(
        private PayrollService $service,
        private AppBillIntegrationService $appBill
    ) {}

    public function index(Request $request): View
    {
        $year = (int) $request->integer('year', now()->year);
        if ($year < 2020 || $year > 2100) $year = now()->year;

        return view('hr.payroll.index', [
            'year' => $year,
            'periods' => PayrollPeriod::forCompany((int) session('company_id'))
                ->where('period_year', $year)
                ->withCount([
                    'slips',
                    'slips as paid_slips_count' => fn ($query) => $query->where('payment_status', 'paid'),
                ])->orderBy('period_month')->get(),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate(['period_year' => ['required', 'integer', 'min:2020', 'max:2100'], 'period_month' => ['required', 'integer', 'between:1,12']]);
        $period = $this->service->generate((int) session('company_id'), (int) $data['period_year'], (int) $data['period_month'], (int) $request->user()->id);
        return redirect()->route('hr.payroll.show', $period)->with('success', 'Draft payroll berhasil dihitung.');
    }

    public function show(Request $request, PayrollPeriod $period): View
    {
        $this->ensureCompany($period);
        $search = trim((string) $request->query('search'));
        $payment = (string) $request->query('payment', 'all');
        $query = $period->slips()->with(['employee', 'branch', 'items']);
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('employee_name_snapshot', 'like', "%{$search}%")
                ->orWhere('employee_no_snapshot', 'like', "%{$search}%")
                ->orWhere('slip_no', 'like', "%{$search}%"));
        }
        if (in_array($payment, ['paid', 'unpaid'], true)) $query->where('payment_status', $payment);

        return view('hr.payroll.show', [
            'period' => $period,
            'slips' => $query->orderBy('employee_name_snapshot')->paginate(30)->withQueryString(),
            'search' => $search,
            'payment' => $payment,
            'paidCount' => $period->slips()->where('payment_status', 'paid')->count(),
            'unpaidCount' => $period->slips()->where('payment_status', '!=', 'paid')->count(),
        ]);
    }

    public function markPaid(Request $request, PayrollSlip $slip): RedirectResponse
    {
        abort_if((int) $slip->company_id !== (int) session('company_id'), 403);
        abort_unless(in_array($slip->status, ['approved', 'published'], true), 422, 'Slip harus disetujui sebelum ditandai sudah dibayar.');
        $data = $request->validate([
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['required', 'date'],
            'payment_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $slip->update([...$data, 'payment_status' => 'paid', 'paid_by' => $request->user()->id]);
        return back()->with('success', "Pembayaran {$slip->employee_name_snapshot} berhasil dicatat.");
    }

    public function undoPayment(Request $request, PayrollSlip $slip): RedirectResponse
    {
        abort_if((int) $slip->company_id !== (int) session('company_id'), 403);
        $slip->update(['payment_status' => 'unpaid', 'payment_reference' => null, 'paid_at' => null, 'paid_by' => null, 'payment_note' => null]);
        return back()->with('success', "Status pembayaran {$slip->employee_name_snapshot} dikembalikan ke belum dibayar.");
    }

    public function export(PayrollPeriod $period): StreamedResponse
    {
        $this->ensureCompany($period);
        $filename = sprintf('payroll-%04d-%02d.csv', $period->period_year, $period->period_month);
        return response()->streamDownload(function () use ($period): void {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['No Slip','NIK','Nama','Branch/Site','Bank','Rekening','Pendapatan','Potongan','Take Home Pay','Status Slip','Status Bayar','Tanggal Bayar','Referensi']);
            $period->slips()->orderBy('employee_name_snapshot')->chunk(200, function ($slips) use ($out): void {
                foreach ($slips as $slip) fputcsv($out, [
                    $slip->slip_no, $slip->employee_no_snapshot, $slip->employee_name_snapshot,
                    $slip->branch_name_snapshot, $slip->bank_name_snapshot, $slip->bank_account_snapshot,
                    $slip->gross_income, $slip->total_deduction, $slip->net_pay, $slip->status,
                    $slip->payment_status, optional($slip->paid_at)->format('Y-m-d H:i:s'), $slip->payment_reference,
                ]);
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function approve(Request $request, PayrollPeriod $period): RedirectResponse
    {
        $this->ensureCompany($period);
        $this->service->approve($period, (int) $request->user()->id);
        return back()->with('success', 'Payroll disetujui HR.');
    }

    public function publish(Request $request, PayrollPeriod $period): RedirectResponse
    {
        $this->ensureCompany($period);
        $period = $this->service->publish($period, (int) $request->user()->id);
        $event = $this->appBill->queuePayrollPeriod($period);
        $message = 'Slip payroll diterbitkan ke OvallHR.';
        if ($event) {
            $message .= ' Payload detail terenkripsi masuk antrean AppBill dummy.';
        }
        return back()->with('success', $message);
    }

    public function payslip(PayrollSlip $slip): View
    {
        abort_if((int) $slip->company_id !== (int) session('company_id'), 403);
        return view('hr.payroll.payslip', ['slip' => $slip->load(['period', 'items'])]);
    }

    private function ensureCompany(PayrollPeriod $period): void
    {
        abort_if((int) $period->company_id !== (int) session('company_id'), 403);
    }
}
