<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // The canonical employees table is created in
        // 2026_07_09_070129_create_oems_core_tables.php. This historical
        // migration used to create a second incompatible employees table and
        // caused MySQL error 1050 on every fresh installation. Later focused
        // migrations extend the canonical table without duplicating it.
    }

    public function down(): void
    {
        // Intentionally empty: the canonical table belongs to the core
        // migration and must not be dropped by this compatibility migration.
    }
};
