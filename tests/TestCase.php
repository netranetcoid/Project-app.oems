<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        // Workspace QA memakai vendor canonical, sehingga inferBasePath milik
        // framework menunjuk C:\laragon. Paksa bootstrap source workspace agar
        // feature test benar-benar menguji patch yang belum disinkronkan.
        $app = require dirname(__DIR__) . '/bootstrap/app.php';

        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
