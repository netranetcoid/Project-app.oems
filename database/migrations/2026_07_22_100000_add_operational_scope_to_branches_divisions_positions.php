<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PT OSM remains the only legal-company record. Operational ownership is
 * modelled underneath it as Branch -> Site, then Division/Position scope.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('branches', 'parent_branch_id')) {
            Schema::table('branches', function (Blueprint $table): void {
                $table->foreignId('parent_branch_id')->nullable()->after('company_id')
                    ->constrained('branches')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('divisions', 'branch_id')) {
            Schema::table('divisions', function (Blueprint $table): void {
                $table->foreignId('branch_id')->nullable()->after('company_id')
                    ->constrained('branches')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('positions', 'branch_id')) {
            Schema::table('positions', function (Blueprint $table): void {
                $table->foreignId('branch_id')->nullable()->after('company_id')
                    ->constrained('branches')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('positions', 'branch_id')) Schema::table('positions', fn (Blueprint $table) => $table->dropConstrainedForeignId('branch_id'));
        if (Schema::hasColumn('divisions', 'branch_id')) Schema::table('divisions', fn (Blueprint $table) => $table->dropConstrainedForeignId('branch_id'));
        if (Schema::hasColumn('branches', 'parent_branch_id')) Schema::table('branches', fn (Blueprint $table) => $table->dropConstrainedForeignId('parent_branch_id'));
    }
};
