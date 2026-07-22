<?php

namespace App\Services\Integration;

use App\Models\AttendanceShift;
use App\Models\Branch;
use App\Models\Company;
use App\Models\IntegrationConnection;
use App\Models\IntegrationInbox;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Membuat atau memperbarui master shift bertanda tangan dari AppBill.
 * Kunci sinkronisasi: company + shift_code; setiap event dicatat di Inbox.
 */
final class AppBillShiftService
{
    public function upsert(Company $company, IntegrationConnection $connection, array $envelope, string $rawHash): array
    {
        return DB::transaction(function () use ($company, $connection, $envelope, $rawHash): array {
            $data = $envelope['data'];
            $sourceRecordId = 'shift:'.$data['shift_code'];
            $incomingVersion = (int) $data['version'];

            $existingInbox = IntegrationInbox::query()
                ->where('company_id', $company->id)
                ->where('provider', 'appbill')
                ->where(function ($query) use ($envelope, $sourceRecordId, $incomingVersion): void {
                    $query->where('external_event_id', $envelope['event_id'])
                        ->orWhere('idempotency_key', $envelope['idempotency_key'])
                        ->orWhere(function ($revision) use ($sourceRecordId, $incomingVersion): void {
                            $revision->where('source_record_id', $sourceRecordId)
                                ->where('source_version', $incomingVersion);
                        });
                })
                ->lockForUpdate()
                ->first();

            if ($existingInbox) {
                if (! hash_equals((string) $existingInbox->payload_hash, $rawHash)) {
                    throw new ConflictHttpException('Event atau versi shift digunakan untuk payload berbeda.');
                }

                return $this->receipt($existingInbox, 'DUPLICATE', true);
            }

            $branch = null;
            if (filled($data['branch_code'] ?? null)) {
                $branch = Branch::query()->where('company_id', $company->id)->where('code', $data['branch_code'])->first();
                if (! $branch) {
                    throw ValidationException::withMessages(['data.branch_code' => ['Kode branch belum ada di AppOEMS.']]);
                }
            }

            // GET /shifts hanya membawa shift_code; karenanya kode harus unik
            // pada satu company, bukan unik per branch/site.
            $shift = AttendanceShift::withTrashed()
                ->where('company_id', $company->id)
                ->where('code', $data['shift_code'])
                ->lockForUpdate()
                ->first();

            $settings = (array) ($shift?->settings ?? []);
            $localVersion = (int) ($settings['appbill_sync_version'] ?? 0);
            if ($shift && $incomingVersion < $localVersion) {
                $inbox = $this->recordInbox($company, $connection, $envelope, $rawHash, $sourceRecordId, $incomingVersion, 'stale');
                return $this->receipt($inbox, 'STALE', true);
            }
            if ($shift && $incomingVersion === $localVersion) {
                if (isset($settings['appbill_payload_hash']) && hash_equals((string) $settings['appbill_payload_hash'], $rawHash)) {
                    $inbox = $this->recordInbox($company, $connection, $envelope, $rawHash, $sourceRecordId, $incomingVersion, 'duplicate');
                    return $this->receipt($inbox, 'DUPLICATE', true);
                }
                throw new ConflictHttpException('Versi shift sama tetapi isi payload berbeda.');
            }

            $settings['appbill_sync_version'] = $incomingVersion;
            $settings['appbill_payload_hash'] = $rawHash;
            $settings['appbill_updated_at'] = $data['updated_at'];
            $settings['appbill_break_minutes'] = (int) ($data['break_minutes'] ?? 0);
            $attributes = [
                'company_id' => $company->id,
                'branch_id' => $branch?->id,
                'code' => $data['shift_code'],
                'name' => $data['shift_name'],
                'work_type' => $data['work_type'] ?? 'office',
                'clock_in_time' => $data['start_time'],
                'clock_out_time' => $data['end_time'],
                'break_start' => null,
                'break_end' => null,
                'work_hours' => $this->workHours($data['start_time'], $data['end_time'], (int) ($data['break_minutes'] ?? 0)),
                'grace_in_minutes' => (int) ($data['grace_in_minutes'] ?? 0),
                'grace_out_minutes' => (int) ($data['grace_out_minutes'] ?? 0),
                'late_tolerance_minutes' => (int) ($data['late_tolerance_minutes'] ?? 0),
                'allow_overtime' => (bool) ($data['allow_overtime'] ?? true),
                'overtime_after_minutes' => (int) ($data['overtime_after_minutes'] ?? 30),
                'overtime_max_minutes' => (int) ($data['overtime_max_minutes'] ?? 180),
                'gps_required' => (bool) ($data['gps_required'] ?? true),
                'selfie_required' => (bool) ($data['selfie_required'] ?? true),
                'photo_required' => (bool) ($data['photo_required'] ?? false),
                'status' => $data['status'] ?? 'active',
                'notes' => $data['notes'] ?? null,
                'settings' => $settings,
            ];

            if ($shift) {
                if ($shift->trashed()) {
                    $shift->restore();
                }
                $shift->forceFill($attributes)->save();
            } else {
                $shift = AttendanceShift::create($attributes);
            }

            $inbox = $this->recordInbox($company, $connection, $envelope, $rawHash, $sourceRecordId, $incomingVersion, 'processed');

            return $this->receipt($inbox, 'ACCEPTED', false, $shift);
        }, 3);
    }

    private function recordInbox(Company $company, IntegrationConnection $connection, array $envelope, string $rawHash, string $sourceRecordId, int $version, string $status): IntegrationInbox
    {
        return IntegrationInbox::create([
            'company_id' => $company->id,
            'integration_connection_id' => $connection->id,
            'provider' => 'appbill',
            'external_event_id' => $envelope['event_id'],
            'idempotency_key' => $envelope['idempotency_key'],
            'payload_hash' => $rawHash,
            'source_record_id' => $sourceRecordId,
            'source_version' => $version,
            'event_type' => $envelope['event'],
            'status' => $status,
            'received_at' => now(),
            'processed_at' => now(),
        ]);
    }

    private function receipt(IntegrationInbox $inbox, string $status, bool $duplicate, ?AttendanceShift $shift = null): array
    {
        return array_filter([
            'status' => $status,
            'sync_id' => "INBOX-{$inbox->id}",
            'duplicate' => $duplicate,
            'shift_code' => $shift?->code,
        ], static fn ($value) => $value !== null);
    }

    private function workHours(string $start, string $end, int $breakMinutes): int
    {
        $from = Carbon::createFromFormat('H:i', $start);
        $to = Carbon::createFromFormat('H:i', $end);
        if ($to->lessThanOrEqualTo($from)) {
            $to->addDay();
        }

        return max(1, (int) ceil(max(0, $from->diffInMinutes($to) - $breakMinutes) / 60));
    }
}
