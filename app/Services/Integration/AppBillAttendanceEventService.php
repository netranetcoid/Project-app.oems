<?php

namespace App\Services\Integration;

use App\Models\Attendance;
use App\Models\AttendanceShift;
use App\Models\Company;
use App\Models\IntegrationConnection;
use App\Models\IntegrationInbox;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class AppBillAttendanceEventService
{
    public function handle(Company $company, IntegrationConnection $connection, array $envelope, string $rawHash): array
    {
        try {
            return DB::transaction(function () use ($company, $connection, $envelope, $rawHash): array {
                $eventId = $envelope['event_id'];
                $idempotencyKey = $envelope['idempotency_key'];
                $data = $envelope['data'];
                $existing = IntegrationInbox::query()
                    ->where('company_id', $company->id)
                    ->where('provider', 'appbill')
                    ->where(function ($query) use ($eventId, $idempotencyKey, $data): void {
                        $query->where('external_event_id', $eventId)
                            ->orWhere('idempotency_key', $idempotencyKey)
                            ->orWhere(function ($revision) use ($data): void {
                                $revision->where('source_record_id', $data['source_record_id'])
                                    ->where('source_version', (int) $data['version']);
                            });
                    })
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    if (! hash_equals((string) $existing->payload_hash, $rawHash)) {
                        throw new ConflictHttpException('Event atau revision digunakan untuk payload berbeda.');
                    }

                    return $this->response($existing, 'DUPLICATE', true);
                }

                $attendance = Attendance::query()
                    ->where('company_id', $company->id)
                    ->where('source_record_id', $data['source_record_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $localVersion = max(1, (int) ($attendance->sync_version ?? 1));
                $incomingVersion = (int) $data['version'];
                $inbox = IntegrationInbox::create([
                    'company_id' => $company->id,
                    'integration_connection_id' => $connection->id,
                    'provider' => 'appbill',
                    'external_event_id' => $eventId,
                    'idempotency_key' => $idempotencyKey,
                    'payload_hash' => $rawHash,
                    'source_record_id' => $data['source_record_id'],
                    'source_version' => $incomingVersion,
                    'event_type' => $envelope['event'],
                    'status' => 'received',
                    'received_at' => now(),
                ]);

                if ($incomingVersion < $localVersion) {
                    $inbox->update(['status' => 'stale', 'processed_at' => now()]);

                    return $this->response($inbox, 'STALE', true);
                }

                if ($incomingVersion === $localVersion) {
                    if ($attendance->external_payload_hash && hash_equals((string) $attendance->external_payload_hash, $rawHash)) {
                        $inbox->update(['status' => 'duplicate', 'processed_at' => now()]);

                        return $this->response($inbox, 'DUPLICATE', true);
                    }
                    throw new ConflictHttpException('Event atau revision digunakan untuk payload berbeda.');
                }

                $shiftId = null;
                if (filled($data['shift_code'] ?? null)) {
                    $shiftId = AttendanceShift::query()
                        ->company((int) $company->id)
                        ->where('code', $data['shift_code'])
                        ->value('id');
                }
                $cancelled = $envelope['event'] === 'attendance.cancelled' || (bool) $data['is_cancelled'];
                $updates = [
                    'clock_in_at' => $this->timestamp($data['check_in'] ?? null),
                    'clock_out_at' => $this->timestamp($data['check_out'] ?? null),
                    'attendance_shift_id' => $shiftId,
                    'status' => $cancelled ? 'incomplete' : $data['status'],
                    'approval_status' => $cancelled ? 'rejected' : $data['approval_status'],
                    'is_cancelled' => $cancelled,
                    'change_reason' => $data['change_reason'] ?? null,
                    'sync_version' => $incomingVersion,
                    'sync_status' => 'synced',
                    'sync_updated_at' => $this->timestamp($data['updated_at']),
                    'source_system' => 'appbill',
                    'external_event_id' => $eventId,
                    'external_revision' => $incomingVersion,
                    'external_payload_hash' => $rawHash,
                    'external_changed_by' => $data['changed_by'],
                    'external_changed_at' => $this->timestamp($envelope['occurred_at']),
                ];

                Attendance::withoutEvents(fn () => $attendance->forceFill($updates)->save());
                $inbox->update(['status' => 'processed', 'processed_at' => now()]);

                return $this->response($inbox, 'ACCEPTED', false);
            }, 3);
        } catch (QueryException $exception) {
            // Unique indexes close the race between two simultaneous requests.
            // Re-read the winner and return the same deterministic result.
            $data = $envelope['data'];
            $winner = IntegrationInbox::query()
                ->where('company_id', $company->id)
                ->where('provider', 'appbill')
                ->where(function ($query) use ($envelope, $data): void {
                    $query->where('external_event_id', $envelope['event_id'])
                        ->orWhere('idempotency_key', $envelope['idempotency_key'])
                        ->orWhere(function ($revision) use ($data): void {
                            $revision->where('source_record_id', $data['source_record_id'])
                                ->where('source_version', (int) $data['version']);
                        });
                })->first();
            if (! $winner) {
                throw $exception;
            }
            if (! hash_equals((string) $winner->payload_hash, $rawHash)) {
                throw new ConflictHttpException('Event atau revision digunakan untuk payload berbeda.');
            }

            return $this->response($winner, 'DUPLICATE', true);
        }
    }

    private function response(IntegrationInbox $inbox, string $status, bool $duplicate): array
    {
        return ['status' => $status, 'sync_id' => "INBOX-{$inbox->id}", 'duplicate' => $duplicate];
    }

    private function timestamp(?string $value): ?Carbon
    {
        return filled($value) ? Carbon::parse($value) : null;
    }
}
