<?php

use App\Domains\Finance\Actions\SeedDefaultDiscountReasons;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_reasons', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'status']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignUlid('discount_reason_id')
                ->nullable()
                ->after('discount_amount')
                ->constrained('discount_reasons')
                ->nullOnDelete();
        });

        $seeder = new SeedDefaultDiscountReasons;
        Organization::query()->pluck('id')->each(fn (string $id) => $seeder->handle($id));
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_reason_id');
        });

        Schema::dropIfExists('discount_reasons');
    }
};
