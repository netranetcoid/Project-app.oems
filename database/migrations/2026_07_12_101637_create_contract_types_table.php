<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_types', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code',30);

            $table->string('name',100);

            $table->integer('default_duration_month')
                ->nullable();

            $table->boolean('is_probation')
                ->default(false);

            $table->boolean('is_permanent')
                ->default(false);

            $table->string('color',30)
                ->default('primary');

            $table->text('description')
                ->nullable();

            $table->json('settings')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->unique([
                'company_id',
                'code'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'contract_types'
        );
    }
};