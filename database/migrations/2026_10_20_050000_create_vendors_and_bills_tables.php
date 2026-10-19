<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'status']);
        });

        Schema::create('vendor_bills', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('client_service_id')->nullable()->constrained('client_services')->nullOnDelete();
            $table->foreignUlid('renewal_id')->nullable()->constrained('renewals')->nullOnDelete();
            $table->string('number');
            $table->string('status')->default('open');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'number']);
            $table->index(['organization_id', 'status']);
            $table->index(['vendor_id', 'status']);
        });

        Schema::create('vendor_bill_payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('vendor_bill_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('other');
            $table->date('paid_on');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vendor_bill_id', 'paid_on']);
        });

        Schema::table('client_services', function (Blueprint $table) {
            $table->foreignUlid('vendor_id')
                ->nullable()
                ->after('provider')
                ->constrained('vendors')
                ->nullOnDelete();
        });

        Schema::table('financial_entries', function (Blueprint $table) {
            $table->foreignUlid('vendor_id')
                ->nullable()
                ->after('vendor')
                ->constrained('vendors')
                ->nullOnDelete();
            $table->foreignUlid('vendor_bill_id')
                ->nullable()
                ->after('vendor_id')
                ->constrained('vendor_bills')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('financial_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_bill_id');
            $table->dropConstrainedForeignId('vendor_id');
        });

        Schema::table('client_services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
        });

        Schema::dropIfExists('vendor_bill_payments');
        Schema::dropIfExists('vendor_bills');
        Schema::dropIfExists('vendors');
    }
};
