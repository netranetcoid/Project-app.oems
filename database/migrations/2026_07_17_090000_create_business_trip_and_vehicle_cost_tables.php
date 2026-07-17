<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('business_trip_policies')) {
            Schema::create('business_trip_policies', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->unique()->constrained('companies')->cascadeOnDelete();
                $table->decimal('daily_allowance', 15, 2)->default(50000);
                $table->decimal('default_monthly_advance', 15, 2)->default(1500000);
                $table->boolean('transport_paid_by_company')->default(true);
                $table->boolean('owner_approval_required')->default(true);
                $table->decimal('hr_delegation_limit', 15, 2)->nullable();
                $table->date('delegation_valid_until')->nullable();
                $table->unsignedSmallInteger('proof_retention_days')->default(60);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('business_trips')) {
            Schema::create('business_trips', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('trip_no', 50);
                $table->string('origin')->nullable();
                $table->string('destination');
                $table->text('purpose');
                $table->date('start_date');
                $table->date('end_date');
                $table->unsignedSmallInteger('total_days');
                $table->decimal('daily_allowance', 15, 2)->default(0);
                $table->decimal('transport_budget', 15, 2)->default(0);
                $table->decimal('lodging_budget', 15, 2)->default(0);
                $table->decimal('other_budget', 15, 2)->default(0);
                $table->decimal('advance_amount', 15, 2)->default(0);
                $table->decimal('actual_amount', 15, 2)->nullable();
                $table->decimal('settlement_difference', 15, 2)->nullable();
                $table->string('status', 30)->default('submitted');
                $table->foreignId('hr_approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('hr_approved_at')->nullable();
                $table->foreignId('owner_approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('owner_approved_at')->nullable();
                $table->boolean('delegation_used')->default(false);
                $table->timestamp('departed_at')->nullable();
                $table->timestamp('returned_at')->nullable();
                $table->timestamp('settled_at')->nullable();
                $table->text('review_note')->nullable();
                $table->json('policy_snapshot')->nullable();
                $table->json('settlement_items')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['company_id', 'trip_no'], 'trip_company_no_uq');
                $table->index(['company_id', 'status'], 'trip_company_status_idx');
                $table->index(['employee_id', 'start_date'], 'trip_employee_date_idx');
            });
        }

        if (! Schema::hasTable('operational_vehicles')) {
            Schema::create('operational_vehicles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
                $table->string('code', 50);
                $table->string('ownership_type', 30)->default('company');
                $table->string('vehicle_type', 30)->default('motorcycle');
                $table->string('plate_no', 30)->nullable();
                $table->string('brand')->nullable();
                $table->string('model')->nullable();
                $table->decimal('monthly_operational_allowance', 15, 2)->default(0);
                $table->decimal('monthly_fuel_budget', 15, 2)->default(0);
                $table->unsignedSmallInteger('service_interval_months')->default(1);
                $table->date('last_service_date')->nullable();
                $table->date('next_service_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['company_id', 'code'], 'vehicle_company_code_uq');
                $table->index(['company_id', 'is_active'], 'vehicle_company_active_idx');
            });
        }

        if (! Schema::hasTable('vehicle_expenses')) {
            Schema::create('vehicle_expenses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('operational_vehicle_id')->constrained('operational_vehicles')->cascadeOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
                $table->unsignedSmallInteger('period_year');
                $table->unsignedTinyInteger('period_month');
                $table->string('type', 30);
                $table->decimal('planned_amount', 15, 2)->default(0);
                $table->decimal('actual_amount', 15, 2)->nullable();
                $table->date('planned_payment_date');
                $table->string('proof_path')->nullable();
                $table->string('status', 30)->default('planned');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['operational_vehicle_id', 'period_year', 'period_month', 'type'], 'vehicle_expense_period_type_uq');
                $table->index(['company_id', 'status'], 'vehicle_exp_company_status_idx');
            });
        }

        if (Schema::hasTable('employee_kpi_assessments') && ! Schema::hasColumn('employee_kpi_assessments', 'payout_date')) {
            Schema::table('employee_kpi_assessments', function (Blueprint $table): void {
                $table->date('payout_date')->nullable()->after('bonus_amount');
                $table->json('source_summary')->nullable()->after('payout_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_kpi_assessments')) {
            Schema::table('employee_kpi_assessments', function (Blueprint $table): void {
                if (Schema::hasColumn('employee_kpi_assessments', 'source_summary')) $table->dropColumn('source_summary');
                if (Schema::hasColumn('employee_kpi_assessments', 'payout_date')) $table->dropColumn('payout_date');
            });
        }
        Schema::dropIfExists('vehicle_expenses');
        Schema::dropIfExists('operational_vehicles');
        Schema::dropIfExists('business_trips');
        Schema::dropIfExists('business_trip_policies');
    }
};
