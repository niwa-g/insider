<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->ulid('organization_id')->nullable()->after('id');
            $table->string('status')->default('active')->after('email');
            $table->decimal('internal_hourly_cost', 12, 2)->nullable()->after('password');
            $table->boolean('must_reset_password')->default(false)->after('internal_hourly_cost');
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'status']);
            $table->dropColumn([
                'organization_id',
                'status',
                'internal_hourly_cost',
                'must_reset_password',
                'deleted_at',
            ]);
        });
    }
};
