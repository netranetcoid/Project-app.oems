<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relasi
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('contract_type_id')
                ->constrained();

            $table->foreignId('parent_contract_id')
                ->nullable()
                ->constrained('employee_contracts')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Nomor Kontrak
            |--------------------------------------------------------------------------
            */

            $table->string('contract_no',100)->unique();

            $table->string('letter_no')->nullable();

            $table->unsignedInteger('contract_sequence')->default(1);

            $table->unsignedInteger('contract_version')->default(1);

            /*
            |--------------------------------------------------------------------------
            | Periode
            |--------------------------------------------------------------------------
            */

            $table->date('start_date');

            $table->date('end_date')->nullable();

            $table->unsignedInteger('duration_month')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status',[
                'draft',
                'waiting',
                'approved',
                'signed',
                'active',
                'expired',
                'terminated',
                'extended'
            ])->default('draft');

            $table->boolean('is_latest')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Snapshot Pegawai
            |--------------------------------------------------------------------------
            */

            $table->string('employee_no')->nullable();

            $table->string('employee_name');

            $table->string('email')->nullable();

            $table->string('phone')->nullable();

            $table->string('branch_name')->nullable();

            $table->string('division_name')->nullable();

            $table->string('position_name')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Snapshot Payroll
            |--------------------------------------------------------------------------
            */

            $table->decimal('basic_salary',15,2)
                ->default(0);

            $table->decimal('meal_allowance',15,2)
                ->default(0);

            $table->decimal('transport_allowance',15,2)
                ->default(0);

            $table->decimal('position_allowance',15,2)
                ->default(0);

            $table->decimal('fixed_allowance',15,2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Dokumen Kontrak
            |--------------------------------------------------------------------------
            */

            $table->longText('contract_body')
                ->nullable();

            $table->string('pdf_file')
                ->nullable();

            $table->string('signature_company')
                ->nullable();

            $table->string('signature_employee')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users');

            $table->timestamp('approved_at')
                ->nullable();

            $table->date('signed_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Lain-lain
            |--------------------------------------------------------------------------
            */

            $table->json('settings')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index([
                'company_id',
                'employee_id'
            ]);

            $table->index([
                'company_id',
                'status'
            ]);

            $table->index([
                'company_id',
                'end_date'
            ]);

            $table->index([
                'company_id',
                'is_latest'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};