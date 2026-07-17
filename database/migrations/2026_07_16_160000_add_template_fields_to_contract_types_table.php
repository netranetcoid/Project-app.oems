<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_types', function (Blueprint $table): void {
            $table->string('template_key', 80)->nullable()->after('code');
            $table->longText('template_body')->nullable()->after('description');
            $table->unsignedInteger('template_version')->default(1)->after('template_body');
        });
    }

    public function down(): void
    {
        Schema::table('contract_types', function (Blueprint $table): void {
            $table->dropColumn(['template_key', 'template_body', 'template_version']);
        });
    }
};
