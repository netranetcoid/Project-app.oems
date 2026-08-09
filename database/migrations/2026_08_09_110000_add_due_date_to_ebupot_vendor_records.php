<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ebupot_vendor_records', function (Blueprint $table): void {
            $table->date('due_date')->nullable()->after('invoice_date');
            $table->index(['company_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('ebupot_vendor_records', function (Blueprint $table): void {
            $table->dropIndex(['company_id', 'due_date']);
            $table->dropColumn('due_date');
        });
    }
};
