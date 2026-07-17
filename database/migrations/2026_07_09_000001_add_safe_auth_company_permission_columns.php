<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    $this->createSessionsTableIfMissing();
    $this->patchUsers();
    $this->patchCompanyUser();
    $this->patchUserLoginLogs();
    $this->patchSpatieTeamsIfAlreadyMigrated();
  }

  public function down(): void
  {
    // Sengaja kosong biar aman. Jangan drop data production.
  }

  private function createSessionsTableIfMissing(): void
  {
    if (Schema::hasTable('sessions')) {
      return;
    }

    Schema::create('sessions', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->unsignedBigInteger('user_id')->nullable()->index();
      $table->string('ip_address', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->longText('payload');
      $table->integer('last_activity')->index();
    });
  }

  private function patchUsers(): void
  {
    if (!Schema::hasTable('users')) {
      return;
    }

    Schema::table('users', function (Blueprint $table) {
      if (!Schema::hasColumn('users', 'company_id')) {
        $table->unsignedBigInteger('company_id')->nullable()->index();
      }

      if (!Schema::hasColumn('users', 'branch_id')) {
        $table->unsignedBigInteger('branch_id')->nullable()->index();
      }

      if (!Schema::hasColumn('users', 'division_id')) {
        $table->unsignedBigInteger('division_id')->nullable()->index();
      }

      if (!Schema::hasColumn('users', 'position_id')) {
        $table->unsignedBigInteger('position_id')->nullable()->index();
      }

      if (!Schema::hasColumn('users', 'google_id')) {
        $table->string('google_id')->nullable()->index();
      }

      if (!Schema::hasColumn('users', 'avatar')) {
        $table->string('avatar')->nullable();
      }

      if (!Schema::hasColumn('users', 'phone')) {
        $table->string('phone', 30)->nullable()->index();
      }

      if (!Schema::hasColumn('users', 'phone_verified_at')) {
        $table->timestamp('phone_verified_at')->nullable();
      }

      if (!Schema::hasColumn('users', 'status')) {
        $table->string('status', 30)->default('active')->index();
      }

      if (!Schema::hasColumn('users', 'is_active')) {
        $table->boolean('is_active')->default(true)->index();
      }

      if (!Schema::hasColumn('users', 'is_locked')) {
        $table->boolean('is_locked')->default(false)->index();
      }

      if (!Schema::hasColumn('users', 'is_super_admin')) {
        $table->boolean('is_super_admin')->default(false)->index();
      }

      if (!Schema::hasColumn('users', 'is_owner')) {
        $table->boolean('is_owner')->default(false)->index();
      }

      if (!Schema::hasColumn('users', 'last_login_at')) {
        $table->timestamp('last_login_at')->nullable()->index();
      }

      if (!Schema::hasColumn('users', 'last_login_ip')) {
        $table->string('last_login_ip', 45)->nullable();
      }

      if (!Schema::hasColumn('users', 'last_login_user_agent')) {
        $table->text('last_login_user_agent')->nullable();
      }

      if (!Schema::hasColumn('users', 'last_failed_login_at')) {
        $table->timestamp('last_failed_login_at')->nullable();
      }

      if (!Schema::hasColumn('users', 'password_changed_at')) {
        $table->timestamp('password_changed_at')->nullable();
      }

      if (!Schema::hasColumn('users', 'locked_at')) {
        $table->timestamp('locked_at')->nullable()->index();
      }

      if (!Schema::hasColumn('users', 'last_activity_at')) {
        $table->timestamp('last_activity_at')->nullable()->index();
      }

      if (!Schema::hasColumn('users', 'preferences')) {
        $table->json('preferences')->nullable();
      }

      if (!Schema::hasColumn('users', 'settings')) {
        $table->json('settings')->nullable();
      }

      if (!Schema::hasColumn('users', 'deleted_at')) {
        $table->softDeletes();
      }
    });
  }

  private function patchCompanyUser(): void
  {
    if (!Schema::hasTable('companies') || !Schema::hasTable('users')) {
      return;
    }

    if (!Schema::hasTable('company_user')) {
      Schema::create('company_user', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('company_id')->index();
        $table->unsignedBigInteger('user_id')->index();
        $table->boolean('is_default')->default(false)->index();
        $table->boolean('is_active')->default(true)->index();
        $table->timestamps();

        $table->unique(['company_id', 'user_id'], 'company_user_company_id_user_id_unique');
      });

      return;
    }

    Schema::table('company_user', function (Blueprint $table) {
      if (!Schema::hasColumn('company_user', 'is_default')) {
        $table->boolean('is_default')->default(false)->index();
      }

      if (!Schema::hasColumn('company_user', 'is_active')) {
        $table->boolean('is_active')->default(true)->index();
      }

      if (!Schema::hasColumn('company_user', 'created_at')) {
        $table->timestamp('created_at')->nullable();
      }

      if (!Schema::hasColumn('company_user', 'updated_at')) {
        $table->timestamp('updated_at')->nullable();
      }
    });
  }

  private function patchUserLoginLogs(): void
  {
    if (Schema::hasTable('user_login_logs')) {
      return;
    }

    Schema::create('user_login_logs', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->nullable()->index();
      $table->unsignedBigInteger('company_id')->nullable()->index();
      $table->string('email')->nullable()->index();
      $table->string('provider', 30)->default('password')->index();
      $table->string('status', 30)->index();
      $table->string('ip_address', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->text('message')->nullable();
      $table->timestamp('logged_in_at')->nullable()->index();
      $table->timestamps();
    });
  }

  private function patchSpatieTeamsIfAlreadyMigrated(): void
  {
    if (!Schema::hasTable('roles')) {
      return;
    }

    $this->patchRolesTable();

    if (Schema::hasTable('model_has_roles')) {
      $this->patchModelHasRolesTable();
    }

    if (Schema::hasTable('model_has_permissions')) {
      $this->patchModelHasPermissionsTable();
    }
  }

  private function patchRolesTable(): void
  {
    if (!Schema::hasColumn('roles', 'company_id')) {
      Schema::table('roles', function (Blueprint $table) {
        $table->unsignedBigInteger('company_id')->nullable()->index()->after('id');
      });
    }

    $this->dropIndexSafely('roles', 'roles_name_guard_name_unique');

    try {
      DB::statement(
        'ALTER TABLE `roles` ADD UNIQUE `roles_company_id_name_guard_name_unique` (`company_id`, `name`, `guard_name`)'
      );
    } catch (\Throwable $e) {
      // Index mungkin sudah ada.
    }
  }

  private function patchModelHasRolesTable(): void
  {
    if (!Schema::hasColumn('model_has_roles', 'company_id')) {
      Schema::table('model_has_roles', function (Blueprint $table) {
        $table->unsignedBigInteger('company_id')->nullable()->index()->after('role_id');
      });
    }

    $defaultCompanyId = $this->defaultCompanyId();

    try {
      DB::table('model_has_roles')
        ->whereNull('company_id')
        ->update(['company_id' => $defaultCompanyId]);
    } catch (\Throwable $e) {
      report($e);
    }

    $this->makeUnsignedBigIntegerNotNullable('model_has_roles', 'company_id');

    $this->rebuildPrimarySafely('model_has_roles', [
      'company_id',
      'role_id',
      'model_id',
      'model_type',
    ]);
  }

  private function patchModelHasPermissionsTable(): void
  {
    if (!Schema::hasColumn('model_has_permissions', 'company_id')) {
      Schema::table('model_has_permissions', function (Blueprint $table) {
        $table->unsignedBigInteger('company_id')->nullable()->index()->after('permission_id');
      });
    }

    $defaultCompanyId = $this->defaultCompanyId();

    try {
      DB::table('model_has_permissions')
        ->whereNull('company_id')
        ->update(['company_id' => $defaultCompanyId]);
    } catch (\Throwable $e) {
      report($e);
    }

    $this->makeUnsignedBigIntegerNotNullable('model_has_permissions', 'company_id');

    $this->rebuildPrimarySafely('model_has_permissions', [
      'company_id',
      'permission_id',
      'model_id',
      'model_type',
    ]);
  }

  private function defaultCompanyId(): int
  {
    if (!Schema::hasTable('companies')) {
      return 1;
    }

    try {
      $query = DB::table('companies');

      if (Schema::hasColumn('companies', 'is_active')) {
        $query->where('is_active', true);
      }

      if (Schema::hasColumn('companies', 'status')) {
        $query->where('status', 'active');
      }

      $company = $query->orderBy('id')->first();

      if ($company) {
        return (int) $company->id;
      }

      $fallback = DB::table('companies')->orderBy('id')->first();

      return $fallback ? (int) $fallback->id : 1;
    } catch (\Throwable $e) {
      return 1;
    }
  }

  private function makeUnsignedBigIntegerNotNullable(string $table, string $column): void
  {
    try {
      DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL");
    } catch (\Throwable $e) {
      report($e);
    }
  }

  private function rebuildPrimarySafely(string $table, array $columns): void
  {
    try {
      DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY");
    } catch (\Throwable $e) {
      // Primary mungkin tidak ada atau sudah berubah.
    }

    try {
      $cols = collect($columns)
        ->map(fn($column) => "`{$column}`")
        ->implode(', ');

      DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY ({$cols})");
    } catch (\Throwable $e) {
      report($e);
    }
  }

  private function dropIndexSafely(string $table, string $index): void
  {
    try {
      DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
    } catch (\Throwable $e) {
      // Index mungkin tidak ada.
    }
  }
};
