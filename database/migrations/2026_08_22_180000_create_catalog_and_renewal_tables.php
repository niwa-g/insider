<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'status']);
        });

        Schema::create('service_types', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('service_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('billing_type')->default('recurring');
            $table->string('billing_cycle')->nullable();
            $table->decimal('default_cost', 12, 2)->default(0);
            $table->decimal('default_selling_price', 12, 2)->default(0);
            $table->string('sla')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'status']);
            $table->index(['service_category_id', 'status']);
        });

        Schema::create('client_services', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('service_type_id')->constrained()->cascadeOnDelete();
            $table->ulid('project_id')->nullable()->index();
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('renewal_date')->nullable()->index();
            $table->string('billing_type')->default('recurring');
            $table->string('billing_cycle')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('recurring_revenue', 12, 2)->default(0);
            $table->string('provider')->nullable();
            $table->foreignId('account_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('technical_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['organization_id', 'renewal_date']);
        });

        Schema::create('renewals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableUlidMorphs('renewable');
            $table->string('title');
            $table->string('renewal_type')->default('service');
            $table->date('renewal_date')->index();
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->string('status')->default('upcoming')->index();
            $table->boolean('auto_renew')->default(false);
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'renewal_date']);
            $table->index(['organization_id', 'status']);
        });

        Schema::create('renewal_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('renewal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_events');
        Schema::dropIfExists('renewals');
        Schema::dropIfExists('client_services');
        Schema::dropIfExists('service_types');
        Schema::dropIfExists('service_categories');
    }
};
