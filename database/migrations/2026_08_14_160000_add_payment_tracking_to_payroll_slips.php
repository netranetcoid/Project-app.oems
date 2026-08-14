<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table): void {
            if (! Schema::hasColumn('payroll_slips', 'payment_status')) {
                $table->string('payment_status', 20)->default('unpaid')->after('status');
                $table->string('payment_reference', 100)->nullable()->after('payment_status');
                $table->timestamp('paid_at')->nullable()->after('payment_reference');
                $table->foreignId('paid_by')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();
                $table->text('payment_note')->nullable()->after('paid_by');
                $table->index(['company_id', 'payment_status'], 'pay_slip_company_payment_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table): void {
            if (Schema::hasColumn('payroll_slips', 'payment_status')) {
                $table->dropIndex('pay_slip_company_payment_idx');
                $table->dropForeign(['paid_by']);
                $table->dropColumn(['payment_status', 'payment_reference', 'paid_at', 'paid_by', 'payment_note']);
            }
        });
    }
};
