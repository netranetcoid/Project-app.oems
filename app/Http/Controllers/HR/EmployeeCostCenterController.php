<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use App\Models\EmployeeReceivable;
use App\Models\EmployeeRequest;
use App\Models\IntegrationOutbox;
use App\Models\PayrollPeriod;
use App\Models\VehicleExpense;
use Illuminate\View\View;

/**
 * Laporan lintas pengeluaran pegawai. Semua sumber selalu dibatasi company_id
 * aktif; halaman ini tidak boleh dipakai untuk membaca biaya perusahaan lain.
 */
class EmployeeCostCenterController extends Controller
{
    public function index(): View
    {
        $companyId = (int) session('company_id');

        $payrollPeriods = PayrollPeriod::query()->forCompany($companyId)
            ->withCount('slips')->latest('period_year')->latest('period_month')->paginate(12, ['*'], 'payroll');
        $publishedPayroll = PayrollPeriod::query()->forCompany($companyId)->where('status', 'published');

        // Reimbursement approved masih kewajiban pembayaran; belum otomatis
        // diperlakukan sebagai kas keluar sampai finance/AppBill mencatat bayar.
        $requests = EmployeeRequest::query()->forCompany($companyId)->with('employee')
            ->whereIn('type', ['reimbursement', 'cash_advance', 'receivable'])
            ->latest('approved_at')->limit(25)->get();
        $reimbursementPayable = EmployeeRequest::query()->forCompany($companyId)
            ->where('type', 'reimbursement')->where('status', 'approved')->sum('approved_amount');

        // Kasbon dan piutang adalah aset/piutang karyawan, bukan beban operasi.
        $receivables = EmployeeReceivable::query()->forCompany($companyId)->with('employee')
            ->where('status', 'active')->latest()->limit(25)->get();

        $trips = BusinessTrip::query()->forCompany($companyId)->with('employee')
            ->latest()->limit(25)->get();
        $tripActual = BusinessTrip::query()->forCompany($companyId)->where('status', 'settled')->sum('actual_amount');
        $tripAdvanceOpen = BusinessTrip::query()->forCompany($companyId)
            ->whereIn('status', ['approved', 'in_progress', 'returned'])->sum('advance_amount');

        $vehicleExpenses = VehicleExpense::query()->forCompany($companyId)->with('vehicle')
            ->latest('planned_payment_date')->limit(25)->get();
        $vehicleActual = VehicleExpense::query()->forCompany($companyId)->whereNotNull('actual_amount')->sum('actual_amount');
        $vehiclePlanned = VehicleExpense::query()->forCompany($companyId)->where('status', 'planned')->sum('planned_amount');

        return view('hr.employee-costs.index', [
            'payrollPeriods' => $payrollPeriods,
            'requests' => $requests,
            'receivables' => $receivables,
            'trips' => $trips,
            'vehicleExpenses' => $vehicleExpenses,
            'appBillEvents' => IntegrationOutbox::query()->forCompany($companyId)
                ->where('event_type', 'payroll.period.published')->latest()->limit(10)->get(),
            'summary' => [
                'payroll_company_burden' => (float) $publishedPayroll->sum('total_company_burden'),
                'payroll_take_home_pay' => (float) $publishedPayroll->sum('total_net'),
                'reimbursement_payable' => (float) $reimbursementPayable,
                'receivable_outstanding' => (float) EmployeeReceivable::query()->forCompany($companyId)->where('status', 'active')->sum('remaining_amount'),
                'trip_actual' => (float) $tripActual,
                'trip_advance_open' => (float) $tripAdvanceOpen,
                'vehicle_actual' => (float) $vehicleActual,
                'vehicle_planned' => (float) $vehiclePlanned,
            ],
        ]);
    }
}
