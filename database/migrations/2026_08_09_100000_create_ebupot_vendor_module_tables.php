<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ebupot_vendors', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40); $table->string('name'); $table->string('npwp', 32)->nullable();
            $table->string('nitku', 32)->nullable(); $table->string('pic_name')->nullable();
            $table->string('whatsapp', 32)->nullable(); $table->string('email')->nullable();
            $table->string('service_name')->nullable(); $table->string('tax_article', 40)->default('PPh Pasal 23');
            $table->string('tax_object_code', 40)->nullable(); $table->decimal('default_tax_rate', 8, 4)->default(2);
            $table->boolean('has_tax_facility')->default(false); $table->text('tax_facility_notes')->nullable();
            $table->boolean('is_active')->default(true); $table->text('notes')->nullable();
            $table->timestamps(); $table->softDeletes(); $table->unique(['company_id','code']);
        });
        Schema::create('ebupot_vendor_records', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ebupot_vendor_id')->constrained('ebupot_vendors')->cascadeOnDelete();
            $table->date('period'); $table->string('invoice_number')->nullable(); $table->date('invoice_date')->nullable();
            $table->decimal('tax_base', 18, 2)->default(0); $table->decimal('tax_rate', 8, 4)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0); $table->decimal('vat_amount', 18, 2)->default(0);
            $table->decimal('stamp_amount', 18, 2)->default(0); $table->decimal('invoice_total', 18, 2)->default(0);
            $table->decimal('net_transfer', 18, 2)->default(0); $table->string('ebupot_number')->nullable();
            $table->date('ebupot_date')->nullable(); $table->string('status', 40)->default('waiting_data');
            $table->json('checklist')->nullable(); $table->boolean('requires_escalation')->default(false);
            $table->text('escalation_reason')->nullable(); $table->text('notes')->nullable();
            $table->string('ebupot_file')->nullable(); $table->string('invoice_file')->nullable();
            $table->string('transfer_file')->nullable(); $table->timestamp('paid_at')->nullable();
            $table->timestamp('sent_at')->nullable(); $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->softDeletes(); $table->unique(['company_id','ebupot_vendor_id','period'], 'ebupot_vendor_period_unique');
        });
        Schema::create('ebupot_vendor_settings', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete()->unique();
            $table->unsignedTinyInteger('payment_deadline_day')->default(15); $table->unsignedTinyInteger('report_deadline_day')->default(20);
            $table->text('wa_initial_template'); $table->text('wa_amount_template'); $table->text('wa_sent_template');
            $table->text('email_template'); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ebupot_vendor_settings'); Schema::dropIfExists('ebupot_vendor_records'); Schema::dropIfExists('ebupot_vendors'); }
};
