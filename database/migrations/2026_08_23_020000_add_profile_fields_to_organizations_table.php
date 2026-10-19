<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('legal_name')->nullable()->after('name');
            $table->string('logo_path')->nullable()->after('slug');
            $table->string('website')->nullable()->after('logo_path');
            $table->string('email')->nullable()->after('website');
            $table->string('phone')->nullable()->after('email');
            $table->string('address_line1')->nullable()->after('phone');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country', 2)->nullable()->after('postal_code');
            $table->string('tax_id')->nullable()->after('country');
            $table->string('primary_color', 7)->nullable()->after('tax_id');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'logo_path',
                'website',
                'email',
                'phone',
                'address_line1',
                'address_line2',
                'city',
                'state',
                'postal_code',
                'country',
                'tax_id',
                'primary_color',
            ]);
        });
    }
};
