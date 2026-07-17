<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kebijakan dibuat sebagai master terpisah agar batas cuti/kasbon dapat
        // diubah HR tanpa mengubah aplikasi mobile atau histori pengajuan lama.
        if (! Schema::hasTable('hr_request_policies')) {
            Schema::create('hr_request_policies', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('type', 40);
                $table->string('name');
                $table->decimal('max_amount', 15, 2)->nullable();
                $table->unsignedSmallInteger('max_days')->nullable();
                $table->unsignedSmallInteger('max_installments')->nullable();
                $table->boolean('requires_document')->default(false);
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'type'], 'hr_policy_company_type_uq');
            });
        }

        if (! Schema::hasTable('employee_requests')) {
            Schema::create('employee_requests', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('request_no', 50);
                $table->string('type', 40);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->decimal('total_days', 8, 2)->nullable();
                $table->decimal('requested_amount', 15, 2)->nullable();
                $table->decimal('approved_amount', 15, 2)->nullable();
                $table->unsignedSmallInteger('installment_count')->nullable();
                $table->text('reason');
                $table->string('document_path')->nullable();
                $table->string('status', 30)->default('submitted');
                $table->timestamp('submitted_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('rejected_at')->nullable();
                $table->text('hr_note')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['company_id', 'request_no'], 'emp_request_company_no_uq');
                $table->index(['company_id', 'status', 'type'], 'emp_request_company_status_idx');
                $table->index(['employee_id', 'submitted_at'], 'emp_request_employee_date_idx');
            });
        }

        if (! Schema::hasTable('employee_receivables')) {
            Schema::create('employee_receivables', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('source_request_id')->nullable()->unique()->constrained('employee_requests')->nullOnDelete();
                $table->string('receivable_no', 50);
                $table->string('type', 30)->default('receivable');
                $table->decimal('principal_amount', 15, 2);
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->decimal('remaining_amount', 15, 2);
                $table->decimal('installment_amount', 15, 2);
                $table->unsignedSmallInteger('installment_count')->default(1);
                $table->date('first_deduction_date')->nullable();
                $table->string('status', 30)->default('active');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'receivable_no'], 'emp_recv_company_no_uq');
                $table->index(['employee_id', 'status'], 'emp_recv_employee_status_idx');
            });
        }

        if (! Schema::hasTable('payroll_periods')) {
            Schema::create('payroll_periods', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->unsignedSmallInteger('period_year');
                $table->unsignedTinyInteger('period_month');
                $table->date('cutoff_date');
                $table->date('salary_payment_date');
                $table->date('kpi_payment_date');
                $table->string('status', 30)->default('draft');
                $table->decimal('total_gross', 18, 2)->default(0);
                $table->decimal('total_deduction', 18, 2)->default(0);
                $table->decimal('total_net', 18, 2)->default(0);
                $table->decimal('total_kpi_bonus', 18, 2)->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('published_at')->nullable();
                $table->json('settings_snapshot')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'period_year', 'period_month'], 'pay_period_company_month_uq');
                $table->index(['company_id', 'status'], 'pay_period_company_status_idx');
            });
        }

        if (! Schema::hasTable('payroll_slips')) {
            Schema::create('payroll_slips', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->string('slip_no', 60);
                $table->string('employee_no_snapshot', 100);
                $table->string('employee_name_snapshot');
                $table->string('branch_name_snapshot')->nullable();
                $table->string('position_name_snapshot')->nullable();
                $table->string('bank_name_snapshot', 100)->nullable();
                $table->string('bank_account_snapshot', 100)->nullable();
                $table->decimal('basic_salary', 15, 2)->default(0);
                $table->decimal('fixed_allowance', 15, 2)->default(0);
                $table->decimal('meal_allowance', 15, 2)->default(0);
                $table->decimal('transport_allowance', 15, 2)->default(0);
                $table->decimal('position_allowance', 15, 2)->default(0);
                $table->decimal('other_income', 15, 2)->default(0);
                $table->decimal('gross_income', 15, 2)->default(0);
                $table->decimal('attendance_deduction', 15, 2)->default(0);
                $table->decimal('receivable_deduction', 15, 2)->default(0);
                $table->decimal('other_deduction', 15, 2)->default(0);
                $table->decimal('total_deduction', 15, 2)->default(0);
                $table->decimal('net_pay', 15, 2)->default(0);
                $table->decimal('kpi_bonus', 15, 2)->default(0);
                $table->date('kpi_payment_date')->nullable();
                $table->string('status', 30)->default('draft');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->json('calculation_snapshot')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'slip_no'], 'pay_slip_company_no_uq');
                $table->unique(['payroll_period_id', 'employee_id'], 'pay_slip_period_employee_uq');
                $table->index(['employee_id', 'status'], 'pay_slip_employee_status_idx');
            });
        }

        if (! Schema::hasTable('payroll_slip_items')) {
            Schema::create('payroll_slip_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('payroll_slip_id')->constrained('payroll_slips')->cascadeOnDelete();
                $table->foreignId('employee_receivable_id')->nullable()->constrained('employee_receivables')->nullOnDelete();
                $table->string('category', 30);
                $table->string('code', 50);
                $table->string('name');
                $table->decimal('amount', 15, 2);
                $table->boolean('is_taxable')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['payroll_slip_id', 'category'], 'pay_item_slip_category_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_slip_items');
        Schema::dropIfExists('payroll_slips');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('employee_receivables');
        Schema::dropIfExists('employee_requests');
        Schema::dropIfExists('hr_request_policies');
    }
};
