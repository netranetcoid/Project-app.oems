<?php

namespace App\Services\HR;

use App\Models\EmployeeRequest;
use App\Models\User;
use App\Notifications\OvallHrEmployeeRequestSubmitted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Satu pintu penerima notifikasi pengajuan mobile.
 *
 * Penerima adalah HR/reviewer dengan permission approval pada company terkait,
 * ditambah owner/developer yang memang memiliki visibility lintas company.
 * Jangan letakkan aturan ini di controller API agar semua pengirim request
 * tetap konsisten saat kanal baru ditambahkan.
 */
class EmployeeRequestNotificationService
{
    public function notifyReviewers(EmployeeRequest $request): void
    {
        // Migration mungkin belum dijalankan pada instalasi lama. Pengajuan
        // tetap tidak boleh gagal hanya karena fitur notifikasi belum aktif.
        if (! Schema::hasTable('notifications')) {
            return;
        }

        try {
            if (function_exists('setPermissionsTeamId')) {
                setPermissionsTeamId((int) $request->company_id);
            }

            $reviewers = User::query()
                ->where('is_active', true)
                ->where(function ($query) use ($request): void {
                    $query->where('company_id', (int) $request->company_id)
                        ->orWhere('is_owner', true)
                        ->orWhere('is_developer', true);
                })
                ->get()
                ->filter(fn (User $user): bool => $user->isActiveUser()
                    && ! $user->isLockedUser()
                    && ($user->is_owner || $user->is_developer || $user->can('hr-request.approve')));

            foreach ($reviewers as $reviewer) {
                $reviewer->notify(new OvallHrEmployeeRequestSubmitted($request));
            }
        } catch (Throwable $exception) {
            // Notifikasi bersifat tambahan; simpan error untuk developer tanpa
            // membatalkan pengajuan pegawai yang sudah tervalidasi.
            Log::warning('Notifikasi pengajuan OvallHR gagal dibuat.', [
                'employee_request_id' => $request->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
