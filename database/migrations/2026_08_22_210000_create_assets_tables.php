<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('type');
            $table->string('website')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('stack')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('status')->default('planned');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index('client_id');
        });

        Schema::create('environments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('application_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('url')->nullable();
            $table->string('status')->default('active');
            $table->string('branch')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index('application_id');
        });

        Schema::create('deployments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('environment_id')->constrained()->cascadeOnDelete();
            $table->string('version')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('deployed_at')->nullable();
            $table->foreignId('deployed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('commit_sha')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index('environment_id');
        });

        Schema::create('domains', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('registrar')->nullable();
            $table->date('registered_at')->nullable();
            $table->date('expires_at')->nullable()->index();
            $table->boolean('auto_renew')->default(false);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index('client_id');
        });

        Schema::create('dns_records', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('domain_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->text('value');
            $table->unsignedInteger('ttl')->default(3600);
            $table->unsignedSmallInteger('priority')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['domain_id', 'status']);
        });

        Schema::create('ssl_certificates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('common_name');
            $table->string('issuer')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable()->index();
            $table->string('status')->default('active');
            $table->boolean('auto_renew')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index('client_id');
        });

        Schema::create('hosting_accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('plan')->nullable();
            $table->string('primary_domain')->nullable();
            $table->string('username')->nullable();
            $table->date('expires_at')->nullable()->index();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index('client_id');
        });

        Schema::create('servers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('hostname')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('role')->nullable();
            $table->string('os')->nullable();
            $table->jsonb('specs')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index('client_id');
        });

        Schema::create('cloud_resources', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('resource_type');
            $table->string('region')->nullable();
            $table->string('external_id')->nullable();
            $table->decimal('monthly_cost', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index('client_id');
        });

        Schema::create('environment_resources', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('environment_id')->constrained()->cascadeOnDelete();
            $table->ulidMorphs('resourceable');
            $table->string('role')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['environment_id', 'resourceable_type', 'resourceable_id'], 'environment_resources_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environment_resources');
        Schema::dropIfExists('cloud_resources');
        Schema::dropIfExists('servers');
        Schema::dropIfExists('hosting_accounts');
        Schema::dropIfExists('ssl_certificates');
        Schema::dropIfExists('dns_records');
        Schema::dropIfExists('domains');
        Schema::dropIfExists('deployments');
        Schema::dropIfExists('environments');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('providers');
    }
};
