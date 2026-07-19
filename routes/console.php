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
