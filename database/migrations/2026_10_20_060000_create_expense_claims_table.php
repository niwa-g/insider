<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_claims', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('claimant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('client_service_id')->nullable()->constrained('client_services')->nullOnDelete();
            $table->foreignUlid('financial_entry_id')->nullable()->constrained('financial_entries')->nullOnDelete();
            $table->string('title');
            $table->string('category')->default('other');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->date('incurred_on');
            $table->string('receipt_path')->nullable();
            $table->text('receipt_name')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->string('payment_method')->nullable();
            $table->date('paid_on')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['claimant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_claims');
    }
};
