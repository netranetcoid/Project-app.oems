<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
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
    } else {
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

    $this->backfillPrimaryCompanyMemberships();
  }

  public function down(): void
  {
    // Data pivot adalah data akses; jangan dihapus saat rollback.
  }

  private function backfillPrimaryCompanyMemberships(): void
  {
    if (!Schema::hasColumn('users', 'company_id')) {
      return;
    }

    DB::table('users')
      ->select(['id', 'company_id'])
      ->whereNotNull('company_id')
      ->orderBy('id')
      ->chunkById(100, function ($users): void {
        foreach ($users as $user) {
          $membershipExists = DB::table('company_user')
            ->where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->exists();

          if ($membershipExists) {
            continue;
          }

          DB::table('company_user')->insert([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
          ]);
        }
      }, 'id');
  }
};
