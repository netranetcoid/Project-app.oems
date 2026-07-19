<?php

namespace App\Notifications;

use App\Models\EmployeeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi database untuk reviewer HR ketika pegawai mengirim pengajuan
 * dari aplikasi OvallHR. Tidak memakai broadcast/queue agar langsung terlihat
 * tanpa kebutuhan service websocket tambahan di VPS.
 */
class OvallHrEmployeeRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(private readonly EmployeeRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $labels = [
            'leave' => 'cuti',
            'permission' => 'izin',
            'sick' => 'sakit',
            'overtime' => 'pengajuan lembur',
            'reimbursement' => 'klaim reimbursement',
            'cash_advance' => 'kasbon',
            'receivable' => 'piutang/cicilan',
        ];
        $employeeName = $this->request->employee?->full_name ?? 'Karyawan';
        $label = $labels[$this->request->type] ?? 'pengajuan';

        return [
            // `kind` dipakai UI agar lonceng hanya menampilkan pekerjaan HR.
            'kind' => 'ovallhr_employee_request',
            'company_id' => (int) $this->request->company_id,
            'employee_request_id' => (int) $this->request->id,
            'request_no' => $this->request->request_no,
            'request_type' => $this->request->type,
            'title' => 'Pengajuan OvallHR baru',
            'message' => sprintf('%s mengajukan %s (%s).', $employeeName, $label, $this->request->request_no),
            'url' => route('hr.requests.index'),
        ];
    }
}
