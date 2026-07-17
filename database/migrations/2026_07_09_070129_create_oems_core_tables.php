<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * CATATAN:
   * - Migration Spatie jangan digabung di sini.
   * - Jalankan migration bawaan Spatie untuk:
   *   roles, permissions, model_has_roles, model_has_permissions, role_has_permissions.
   */
  public function up(): void
  {
    /*
        |--------------------------------------------------------------------------
        | COMPANIES
        |--------------------------------------------------------------------------
        | Master perusahaan. Disiapkan untuk multi company / multi tenant.
        */
    Schema::create('companies', function (Blueprint $table) {
      $table->id();

      // Identitas utama
      $table->string('code', 50)->unique();
      $table->string('name');
      $table->string('legal_name')->nullable();
      $table->string('brand_name')->nullable();
      $table->string('business_type', 100)->nullable();
      $table->string('industry_type', 100)->nullable();

      // Legalitas
      $table->string('npwp', 50)->nullable();
      $table->string('nib', 100)->nullable();
      $table->string('siup', 100)->nullable();
      $table->string('tdp', 100)->nullable();
      $table->string('akta_no', 100)->nullable();
      $table->date('akta_date')->nullable();

      // Kontak
      $table->string('email')->nullable();
      $table->string('phone', 50)->nullable();
      $table->string('mobile_phone', 50)->nullable();
      $table->string('website')->nullable();

      // Alamat
      $table->text('address')->nullable();
      $table->string('province', 100)->nullable();
      $table->string('city', 100)->nullable();
      $table->string('district', 100)->nullable();
      $table->string('village', 100)->nullable();
      $table->string('postal_code', 20)->nullable();

      // Logo & branding
      $table->string('logo')->nullable();
      $table->string('favicon')->nullable();
      $table->string('primary_color', 20)->nullable();
      $table->string('secondary_color', 20)->nullable();

      // Bank perusahaan
      $table->string('bank_name', 100)->nullable();
      $table->string('bank_account_no', 100)->nullable();
      $table->string('bank_account_name')->nullable();

      // Setting payroll dasar
      $table->unsignedTinyInteger('salary_payment_day')->nullable();
      $table->string('salary_calculation_type', 50)->default('monthly');
      $table->string('default_currency', 10)->default('IDR');

      // Setting attendance dasar
      $table->string('timezone', 100)->default('Asia/Jakarta');
      $table->boolean('attendance_gps_required')->default(false);
      $table->unsignedInteger('attendance_radius_meter')->default(100);

      // Setting sistem
      $table->string('status', 50)->default('active');
      // active, inactive, suspended

      $table->json('settings')->nullable();
      $table->text('notes')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->index(['status']);
      $table->index(['name']);
    });

    /*
        |--------------------------------------------------------------------------
        | BRANCHES
        |--------------------------------------------------------------------------
        | Cabang / lokasi kerja.
        */
    Schema::create('branches', function (Blueprint $table) {
      $table->id();

      $table->foreignId('company_id')
        ->constrained('companies')
        ->cascadeOnDelete();

      $table->string('code', 50);
      $table->string('name');
      $table->string('type', 50)->default('branch');
      // head_office, branch, site, warehouse, project

      // Kontak
      $table->string('email')->nullable();
      $table->string('phone', 50)->nullable();
      $table->string('mobile_phone', 50)->nullable();

      // Alamat
      $table->text('address')->nullable();
      $table->string('province', 100)->nullable();
      $table->string('city', 100)->nullable();
      $table->string('district', 100)->nullable();
      $table->string('village', 100)->nullable();
      $table->string('postal_code', 20)->nullable();

      // Koordinat untuk absensi GPS
      $table->decimal('latitude', 11, 8)->nullable();
      $table->decimal('longitude', 11, 8)->nullable();
      $table->unsignedInteger('attendance_radius_meter')->nullable();

      // Operasional
      $table->string('timezone', 100)->default('Asia/Jakarta');
      $table->date('opened_at')->nullable();
      $table->date('closed_at')->nullable();

      // PIC branch, sengaja belum FK ke users supaya tidak circular
      $table->unsignedBigInteger('pic_user_id')->nullable();
      $table->string('pic_name')->nullable();
      $table->string('pic_phone', 50)->nullable();

      $table->string('status', 50)->default('active');
      // active, inactive, closed

      $table->json('settings')->nullable();
      $table->text('notes')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->unique(['company_id', 'code']);
      $table->index(['company_id', 'status']);
      $table->index(['name']);
    });

    /*
        |--------------------------------------------------------------------------
        | DIVISIONS
        |--------------------------------------------------------------------------
        | Divisi / department. Bisa parent-child.
        */
    Schema::create('divisions', function (Blueprint $table) {
      $table->id();

      $table->foreignId('company_id')
        ->constrained('companies')
        ->cascadeOnDelete();

      $table->foreignId('parent_id')
        ->nullable()
        ->constrained('divisions')
        ->nullOnDelete();

      $table->string('code', 50);
      $table->string('name');
      $table->string('type', 100)->nullable();
      // management, finance, hr, noc, teknisi, marketing, sales, warehouse

      $table->text('description')->nullable();

      // Kepala divisi, sengaja belum FK ke users
      $table->unsignedBigInteger('head_user_id')->nullable();
      $table->string('head_name')->nullable();

      // KPI & payroll integration
      $table->boolean('is_kpi_enabled')->default(true);
      $table->boolean('is_payroll_enabled')->default(true);
      $table->boolean('is_attendance_required')->default(true);

      $table->unsignedInteger('sort_order')->default(0);
      $table->string('status', 50)->default('active');
      // active, inactive

      $table->json('settings')->nullable();
      $table->text('notes')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->unique(['company_id', 'code']);
      $table->index(['company_id', 'parent_id']);
      $table->index(['company_id', 'status']);
      $table->index(['name']);
    });

    /*
        |--------------------------------------------------------------------------
        | POSITIONS
        |--------------------------------------------------------------------------
        | Jabatan / type jabatan. Bisa dipakai buat level approval, KPI, payroll.
        */
    Schema::create('positions', function (Blueprint $table) {
      $table->id();

      $table->foreignId('company_id')
        ->constrained('companies')
        ->cascadeOnDelete();

      $table->foreignId('division_id')
        ->nullable()
        ->constrained('divisions')
        ->nullOnDelete();

      $table->foreignId('parent_id')
        ->nullable()
        ->constrained('positions')
        ->nullOnDelete();

      $table->string('code', 50);
      $table->string('name');

      // Level organisasi
      $table->unsignedInteger('level')->default(1);
      // contoh: 1 staff, 2 senior, 3 leader, 4 supervisor, 5 manager, 9 director

      $table->string('grade', 50)->nullable();
      $table->string('type', 100)->nullable();
      // staff, leader, supervisor, manager, director, owner

      $table->text('description')->nullable();

      // Approval & akses
      $table->boolean('is_approver')->default(false);
      $table->boolean('is_management')->default(false);
      $table->boolean('is_field_worker')->default(false);

      // KPI & payroll
      $table->boolean('is_kpi_enabled')->default(true);
      $table->boolean('is_payroll_enabled')->default(true);
      $table->decimal('default_basic_salary', 15, 2)->nullable();
      $table->decimal('default_allowance', 15, 2)->nullable();
      $table->decimal('default_kpi_incentive_max', 15, 2)->nullable();

      $table->unsignedInteger('sort_order')->default(0);
      $table->string('status', 50)->default('active');
      // active, inactive

      $table->json('settings')->nullable();
      $table->text('notes')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->unique(['company_id', 'code']);
      $table->index(['company_id', 'division_id']);
      $table->index(['company_id', 'level']);
      $table->index(['company_id', 'status']);
      $table->index(['name']);
    });

    /*
        |--------------------------------------------------------------------------
        | EMPLOYEES
        |--------------------------------------------------------------------------
        | Data karyawan. Tidak semua employee harus punya login.
        */
    Schema::create('employees', function (Blueprint $table) {
      $table->id();

      // Relasi organisasi
      $table->foreignId('company_id')
        ->constrained('companies')
        ->cascadeOnDelete();

      $table->foreignId('branch_id')
        ->nullable()
        ->constrained('branches')
        ->nullOnDelete();

      $table->foreignId('division_id')
        ->nullable()
        ->constrained('divisions')
        ->nullOnDelete();

      $table->foreignId('position_id')
        ->nullable()
        ->constrained('positions')
        ->nullOnDelete();

      // Identitas karyawan
      $table->string('employee_no', 100);
      $table->string('name');
      $table->string('nickname')->nullable();
      $table->string('email')->nullable();
      $table->string('personal_email')->nullable();
      $table->string('phone', 50)->nullable();
      $table->string('whatsapp', 50)->nullable();

      // Data pribadi
      $table->string('gender', 20)->nullable();
      // male, female

      $table->string('birth_place', 100)->nullable();
      $table->date('birth_date')->nullable();
      $table->string('religion', 50)->nullable();
      $table->string('marital_status', 50)->nullable();
      $table->unsignedTinyInteger('number_of_dependents')->default(0);

      // Identitas legal
      $table->string('identity_type', 50)->default('ktp');
      $table->string('identity_number', 100)->nullable();
      $table->string('npwp', 100)->nullable();
      $table->string('kk_number', 100)->nullable();

      // Alamat
      $table->text('address')->nullable();
      $table->text('domicile_address')->nullable();
      $table->string('province', 100)->nullable();
      $table->string('city', 100)->nullable();
      $table->string('district', 100)->nullable();
      $table->string('village', 100)->nullable();
      $table->string('postal_code', 20)->nullable();

      // Kontak darurat
      $table->string('emergency_contact_name')->nullable();
      $table->string('emergency_contact_relation', 100)->nullable();
      $table->string('emergency_contact_phone', 50)->nullable();
      $table->text('emergency_contact_address')->nullable();

      // Status kerja
      $table->string('employment_status', 50)->default('permanent');
      // permanent, contract, probation, freelance, internship, outsource

      $table->string('work_status', 50)->default('active');
      // active, inactive, suspended, resigned, terminated

      $table->date('join_date')->nullable();
      $table->date('probation_end_date')->nullable();
      $table->date('contract_start_date')->nullable();
      $table->date('contract_end_date')->nullable();
      $table->date('resign_date')->nullable();
      $table->text('resign_reason')->nullable();

      // Supervisor / atasan, sengaja belum FK supaya fleksibel
      $table->unsignedBigInteger('supervisor_employee_id')->nullable();
      $table->unsignedBigInteger('manager_employee_id')->nullable();

      // Payroll dasar
      $table->decimal('basic_salary', 15, 2)->default(0);
      $table->decimal('daily_salary', 15, 2)->nullable();
      $table->decimal('hourly_salary', 15, 2)->nullable();
      $table->decimal('fixed_allowance', 15, 2)->default(0);
      $table->decimal('meal_allowance', 15, 2)->default(0);
      $table->decimal('transport_allowance', 15, 2)->default(0);
      $table->decimal('position_allowance', 15, 2)->default(0);

      // KPI / incentive
      $table->boolean('is_kpi_enabled')->default(true);
      $table->decimal('kpi_incentive_max', 15, 2)->nullable();
      $table->string('kpi_category_code', 100)->nullable();

      // Bank karyawan
      $table->string('bank_name', 100)->nullable();
      $table->string('bank_account_no', 100)->nullable();
      $table->string('bank_account_name')->nullable();

      // BPJS / pajak
      $table->string('bpjs_kesehatan_no', 100)->nullable();
      $table->string('bpjs_ketenagakerjaan_no', 100)->nullable();
      $table->boolean('is_bpjs_kesehatan_active')->default(false);
      $table->boolean('is_bpjs_ketenagakerjaan_active')->default(false);
      $table->string('tax_status', 50)->nullable();
      // TK/0, K/0, K/1, dll

      // Attendance
      $table->boolean('is_attendance_required')->default(true);
      $table->string('attendance_type', 50)->default('normal');
      // normal, shift, flexible, remote

      $table->string('work_location_type', 50)->default('office');
      // office, field, remote, hybrid

      // Dokumen / foto
      $table->string('photo')->nullable();
      $table->string('identity_photo')->nullable();
      $table->string('signature')->nullable();

      // Metadata
      $table->json('custom_fields')->nullable();
      $table->json('settings')->nullable();
      $table->text('notes')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->unique(['company_id', 'employee_no']);
      $table->index(['company_id', 'branch_id']);
      $table->index(['company_id', 'division_id']);
      $table->index(['company_id', 'position_id']);
      $table->index(['company_id', 'employment_status']);
      $table->index(['company_id', 'work_status']);
      $table->index(['name']);
      $table->index(['email']);
      $table->index(['phone']);
    });

    /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        | Akun login aplikasi.
        | Role & permission tetap dari Spatie.
        */
    Schema::create('users', function (Blueprint $table) {
      $table->id();

      // Relasi ke struktur perusahaan
      $table->foreignId('company_id')
        ->nullable()
        ->constrained('companies')
        ->nullOnDelete();

      $table->foreignId('branch_id')
        ->nullable()
        ->constrained('branches')
        ->nullOnDelete();

      $table->foreignId('division_id')
        ->nullable()
        ->constrained('divisions')
        ->nullOnDelete();

      $table->foreignId('position_id')
        ->nullable()
        ->constrained('positions')
        ->nullOnDelete();

      $table->foreignId('employee_id')
        ->nullable()
        ->constrained('employees')
        ->nullOnDelete();

      // Identitas login
      $table->string('name');
      $table->string('username')->nullable()->unique();
      $table->string('email')->nullable()->unique();
      $table->string('phone', 50)->nullable()->unique();

      $table->timestamp('email_verified_at')->nullable();
      $table->timestamp('phone_verified_at')->nullable();

      $table->string('password')->nullable();

      // Google login / social login
      $table->string('google_id')->nullable()->unique();
      $table->string('google_token')->nullable();
      $table->string('google_refresh_token')->nullable();

      // Avatar & profile
      $table->string('avatar')->nullable();
      $table->string('cover_photo')->nullable();
      $table->string('language', 20)->default('id');
      $table->string('timezone', 100)->default('Asia/Jakarta');

      // Security
      $table->boolean('is_super_admin')->default(false);
      $table->boolean('is_owner')->default(false);
      $table->boolean('is_active')->default(true);
      $table->boolean('is_locked')->default(false);
      $table->timestamp('locked_at')->nullable();
      $table->text('locked_reason')->nullable();

      $table->unsignedInteger('failed_login_attempts')->default(0);
      $table->timestamp('last_failed_login_at')->nullable();
      $table->timestamp('password_changed_at')->nullable();

      // 2FA siap pakai kalau nanti dibutuhkan
      $table->text('two_factor_secret')->nullable();
      $table->text('two_factor_recovery_codes')->nullable();
      $table->timestamp('two_factor_confirmed_at')->nullable();

      // Tracking login
      $table->timestamp('last_login_at')->nullable();
      $table->string('last_login_ip', 100)->nullable();
      $table->text('last_login_user_agent')->nullable();
      $table->timestamp('last_activity_at')->nullable();

      // Status
      $table->string('status', 50)->default('active');
      // active, inactive, suspended, invited

      $table->timestamp('invited_at')->nullable();
      $table->timestamp('accepted_invitation_at')->nullable();

      // Preferences
      $table->json('preferences')->nullable();
      $table->json('settings')->nullable();

      $table->rememberToken();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['company_id', 'branch_id']);
      $table->index(['company_id', 'division_id']);
      $table->index(['company_id', 'position_id']);
      $table->index(['company_id', 'employee_id']);
      $table->index(['company_id', 'status']);
      $table->index(['is_super_admin']);
      $table->index(['is_active']);
      $table->index(['last_login_at']);
    });

    /*
        |--------------------------------------------------------------------------
        | MODULES
        |--------------------------------------------------------------------------
        | Modul aplikasi untuk sidebar / permission grouping.
        */
    Schema::create('modules', function (Blueprint $table) {
      $table->id();

      $table->string('code', 100)->unique();
      $table->string('name');
      $table->string('label')->nullable();
      $table->text('description')->nullable();

      $table->string('icon')->nullable();
      $table->string('url')->nullable();
      $table->string('route_name')->nullable();

      $table->string('group', 100)->nullable();
      // core, hr, finance, kpi, payroll, settings

      $table->unsignedInteger('sort_order')->default(0);

      $table->boolean('is_active')->default(true);
      $table->boolean('is_visible')->default(true);
      $table->boolean('is_system')->default(false);

      $table->json('settings')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->index(['group']);
      $table->index(['is_active']);
      $table->index(['is_visible']);
      $table->index(['sort_order']);
    });

    /*
        |--------------------------------------------------------------------------
        | MENUS
        |--------------------------------------------------------------------------
        | Menu/sidebar dinamis.
        | permission_name diisi dari tabel permissions Spatie.
        */
    Schema::create('menus', function (Blueprint $table) {
      $table->id();

      $table->foreignId('module_id')
        ->nullable()
        ->constrained('modules')
        ->nullOnDelete();

      $table->foreignId('parent_id')
        ->nullable()
        ->constrained('menus')
        ->nullOnDelete();

      $table->string('code', 150)->unique();
      $table->string('name');
      $table->string('label')->nullable();

      $table->string('type', 50)->default('menu');
      // header, menu, divider, action

      $table->string('icon')->nullable();
      $table->string('url')->nullable();
      $table->string('route_name')->nullable();

      // Ini nyambung ke Spatie permission name, tidak dibuat FK biar fleksibel.
      $table->string('permission_name')->nullable();

      // UI helper
      $table->string('target', 50)->default('_self');
      $table->string('badge_text', 50)->nullable();
      $table->string('badge_color', 50)->nullable();

      $table->unsignedInteger('sort_order')->default(0);
      $table->unsignedTinyInteger('level')->default(1);

      $table->boolean('is_active')->default(true);
      $table->boolean('is_visible')->default(true);
      $table->boolean('is_system')->default(false);
      $table->boolean('open_in_new_tab')->default(false);

      $table->json('meta')->nullable();
      $table->json('settings')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->index(['module_id', 'parent_id']);
      $table->index(['permission_name']);
      $table->index(['type']);
      $table->index(['is_active']);
      $table->index(['is_visible']);
      $table->index(['sort_order']);
    });

    /*
        |--------------------------------------------------------------------------
        | USER LOGIN LOGS
        |--------------------------------------------------------------------------
        | Riwayat login/logout user.
        */
    if (!Schema::hasTable('user_login_logs')) {
      Schema::create('user_login_logs', function (Blueprint $table) {
        $table->id();

        $table->foreignId('company_id')
          ->nullable()
          ->constrained('companies')
          ->nullOnDelete();

        $table->foreignId('user_id')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();

        $table->foreignId('employee_id')
          ->nullable()
          ->constrained('employees')
          ->nullOnDelete();

        $table->string('login_identifier')->nullable();
        $table->string('login_method', 50)->default('password');

        $table->boolean('is_success')->default(false);
        $table->string('status', 50)->default('failed');
        $table->text('failure_reason')->nullable();

        $table->string('ip_address', 100)->nullable();
        $table->text('user_agent')->nullable();
        $table->string('device_type', 100)->nullable();
        $table->string('device_name')->nullable();
        $table->string('browser', 100)->nullable();
        $table->string('platform', 100)->nullable();

        $table->string('country', 100)->nullable();
        $table->string('city', 100)->nullable();
        $table->decimal('latitude', 11, 8)->nullable();
        $table->decimal('longitude', 11, 8)->nullable();

        $table->timestamp('login_at')->nullable();
        $table->timestamp('logout_at')->nullable();
        $table->unsignedInteger('session_duration_seconds')->nullable();

        $table->string('session_id')->nullable();
        $table->boolean('is_current_session')->default(false);

        $table->json('meta')->nullable();

        $table->timestamps();

        $table->index(['company_id', 'user_id']);
        $table->index(['company_id', 'employee_id']);
        $table->index(['login_identifier']);
        $table->index(['login_method']);
        $table->index(['is_success']);
        $table->index(['status']);
        $table->index(['login_at']);
        $table->index(['ip_address']);
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_login_logs');
    Schema::dropIfExists('menus');
    Schema::dropIfExists('modules');
    Schema::dropIfExists('users');
    Schema::dropIfExists('employees');
    Schema::dropIfExists('positions');
    Schema::dropIfExists('divisions');
    Schema::dropIfExists('branches');
    Schema::dropIfExists('companies');
  }
};
