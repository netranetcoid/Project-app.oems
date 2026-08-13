<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\Employee\OwnerMobileQaProvisioner;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Safe interactive provisioning for the Owner's own mobile QA account. The
// password is read without echoing and never placed in Git, logs, or history.
Artisan::command('oems:create-owner-mobile-qa {--email=owner@oems.local} {--username=owner} {--name=Owner QA}', function () {
    $password = (string) $this->secret('Password akun Owner QA (minimal 12 karakter)');
    $confirm = (string) $this->secret('Ulangi password');
    if ($password !== $confirm || strlen($password) < 12) {
        $this->error('Password tidak sama atau kurang dari 12 karakter. Tidak ada data dibuat.');
        return 1;
    }
    $result = app(OwnerMobileQaProvisioner::class)->provision(
        (string) $this->option('name'), (string) $this->option('email'), (string) $this->option('username'), $password
    );
    $this->info('Selesai. Login OvallHR: ' . $result['user']->username . ' atau ' . $result['user']->email);
    $this->line('Akun terhubung ke employee QA, shift fleksibel, dan kebijakan Bebas Lokasi.');
    return 0;
})->purpose('Create or refresh the secure Owner QA account for OvallHR');
// Sinkronisasi pegawai aktif lama ke akun OvallHR. Aman diulang: akun lama
// tetap memakai passwordnya, akun yang belum ada diberi password sementara.
Artisan::command('oems:sync-mobile-accounts {--company=}', function () {
    $companyId = $this->option('company');
    $query = \App\Models\Employee::query()
        ->where('work_status', 'active')
        ->whereNotNull('email')
        ->where('email', '!=', '');

    if ($companyId) {
        $query->where('company_id', (int) $companyId);
    }

    $service = app(\App\Services\Employee\EmployeeUserService::class);
    $created = 0;
    $failed = 0;

    $query->orderBy('id')->each(function (\App\Models\Employee $employee) use ($service, &$created, &$failed): void {
        try {
            $user = $service->create($employee->toArray());
            if ((int) $employee->user_id !== (int) $user->id) {
                $employee->forceFill(['user_id' => $user->id])->save();
            }
            $created++;
        } catch (\Throwable $exception) {
            $failed++;
            $this->warn($employee->email . ': ' . $exception->getMessage());
        }
    });

    $this->info("Selesai: {$created} akun ditautkan, {$failed} gagal.");
})->purpose('Create or link OvallHR accounts for active employees with email');
\Illuminate\Support\Facades\Schedule::call(fn () => app(\App\Services\Employee\CleaningDutyPublisher::class)->publishForToday())->name('publish-cleaning-duty')->everyFifteenMinutes()->withoutOverlapping();
