<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('provider');
            $table->string('category');
            $table->string('status')->default('disconnected');
            $table->jsonb('config')->nullable();
            $table->text('credentials_encrypted')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->foreignId('connected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'provider', 'name']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'category']);
        });

        Schema::create('integration_sync_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('integration_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->string('status');
            $table->text('message')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['organization_id', 'integration_id']);
            $table->index(['integration_id', 'created_at']);
        });

        Schema::create('integration_external_refs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('integration_id')->constrained()->cascadeOnDelete();
            $table->string('external_type');
            $table->string('external_id');
            $table->string('external_name')->nullable();
            $table->string('local_type')->nullable();
            $table->ulid('local_id')->nullable();
            $table->jsonb('payload')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['integration_id', 'external_type', 'external_id']);
            $table->index(['organization_id', 'external_type']);
            $table->index(['local_type', 'local_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_external_refs');
        Schema::dropIfExists('integration_sync_logs');
        Schema::dropIfExists('integrations');
    }
};
