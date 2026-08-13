<?php

namespace App\Services\Employee;

use App\Models\CleaningDutyLog;
use App\Models\CleaningDutyLogItem;
use App\Models\CleaningDutySchedule;
use App\Models\Company;
use App\Models\EmployeeTask;
use App\Models\MobileAnnouncement;
use Carbon\CarbonImmutable;

class CleaningDutyPublisher
{
    public function publishForToday(): int
    {
        $count = 0;
        Company::query()->select(['id', 'timezone'])->each(function (Company $company) use (&$count): void {
            $timezone = in_array($company->timezone, timezone_identifiers_list(), true) ? $company->timezone : 'Asia/Jakarta';
            $today = CarbonImmutable::now($timezone)->startOfDay();

            CleaningDutyLog::query()->forCompany($company->id)->whereIn('status', ['pending', 'in_progress', 'submitted'])
                ->whereDate('duty_date', '<', $today->toDateString())->get()->each(function (CleaningDutyLog $log): void {
                    $log->update(['status' => 'missed']);
                    $log->task?->update(['status' => 'cancelled']);
                    $this->syncAnnouncements($log->fresh(['schedule', 'division', 'employee']));
                });

            CleaningDutySchedule::query()->forCompany($company->id)->with(['division:id,name', 'employee:id,name,employee_no', 'items'])
                ->where('is_active', true)->whereDate('starts_on', '<=', $today->toDateString())
                ->where(fn ($q) => $q->whereNull('ends_on')->orWhereDate('ends_on', '>=', $today->toDateString()))
                ->get()->filter(fn (CleaningDutySchedule $schedule) => $this->isDue($schedule, $today))
                ->each(function (CleaningDutySchedule $schedule) use ($company, $today, &$count): void {
                    $log = CleaningDutyLog::query()->firstOrCreate(
                        ['cleaning_duty_schedule_id' => $schedule->id, 'duty_date' => $today->toDateString()],
                        ['company_id' => $company->id, 'division_id' => $schedule->division_id, 'employee_id' => $schedule->employee_id]
                    );
                    if (! $log->employee_task_id) {
                        $task = EmployeeTask::query()->create([
                            'company_id' => $company->id,
                            'employee_id' => $schedule->employee_id,
                            'title' => $schedule->title,
                            'description' => $this->taskDescription($schedule),
                            'priority' => $schedule->duty_type === 'server_room' ? 'high' : 'normal',
                            'status' => 'assigned',
                            'due_at' => $today->endOfDay()->utc(),
                        ]);
                        $log->update(['employee_task_id' => $task->id]);
                    }
                    foreach ($schedule->items->where('is_active', true) as $item) {
                        CleaningDutyLogItem::query()->firstOrCreate(['cleaning_duty_log_id' => $log->id, 'cleaning_duty_item_id' => $item->id]);
                    }
                    $this->syncAnnouncements($log->fresh(['schedule', 'division', 'employee']));
                    $count++;
                });
        });
        return $count;
    }

    public function syncAnnouncements(CleaningDutyLog $log): void
    {
        $schedule = $log->schedule;
        $name = $log->employee?->name ?: 'Pegawai';
        $division = $log->division?->name ?: 'Divisi';
        $status = match ($log->status) {
            'completed' => 'SELESAI', 'submitted' => 'MENUNGGU VERIFIKASI HR', 'in_progress' => 'SEDANG DIKERJAKAN',
            'missed' => 'TIDAK DIKERJAKAN', default => 'BELUM DIKERJAKAN',
        };
        $expires = $log->duty_date->copy()->endOfDay()->utc();
        MobileAnnouncement::query()->updateOrCreate(
            ['company_id' => $log->company_id, 'source_type' => 'cleaning_duty_public', 'source_id' => $log->id],
            ['division_id' => null, 'employee_id' => null, 'title' => 'PIKET HARI INI - ' . $division,
                'message' => $name . ' - ' . $schedule->title . '. Status: ' . $status . '.', 'is_active' => true,
                'published_at' => $log->duty_date->copy()->startOfDay()->utc(), 'expires_at' => $expires]
        );
        MobileAnnouncement::query()->updateOrCreate(
            ['company_id' => $log->company_id, 'source_type' => 'cleaning_duty_assignee', 'source_id' => $log->id],
            ['division_id' => null, 'employee_id' => $log->employee_id, 'title' => 'TUGAS PIKET ANDA HARI INI',
                'message' => $schedule->title . '. Buka menu Tugas dan selesaikan sebelum akhir hari. Status Anda: ' . $status . '.',
                'is_active' => ! in_array($log->status, ['completed', 'missed'], true),
                'published_at' => $log->duty_date->copy()->startOfDay()->utc(), 'expires_at' => $expires]
        );
    }

    private function isDue(CleaningDutySchedule $schedule, CarbonImmutable $today): bool
    {
        return $schedule->recurrence === 'monthly'
            ? (int) $schedule->day_of_month === $today->day
            : (int) $schedule->weekday === $today->isoWeekday();
    }

    private function taskDescription(CleaningDutySchedule $schedule): string
    {
        $items = $schedule->items->where('is_active', true)->map(fn ($item) => '- ' . $item->item_name . ' (bobot ' . $item->weight . '%)')->implode("\n");
        return trim(($schedule->instructions ?: 'Kerjakan sesuai SOP kebersihan perusahaan.') . ($items ? "\n\nChecklist:\n" . $items : ''));
    }
}
