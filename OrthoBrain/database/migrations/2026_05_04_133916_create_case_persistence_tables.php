<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update cases table with impressions data
        Schema::table('cases', function (Blueprint $table) {
            $table->foreignId('scanner_id')->nullable()->after('practice_id')->constrained('scanners')->nullOnDelete();
            $table->enum('impression_method', ['DIGITAL', 'PHYSICAL'])->nullable()->after('scanner_id');
        });

        // 2. Create case_additional_info table
        Schema::create('case_additional_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->json('data')->nullable(); // Stores the entire checklist state
            $table->timestamps();
        });

        // 3. Create case_shipping_addresses table
        Schema::create('case_shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('practice_name')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('street_address_1')->nullable();
            $table->string('street_address_2')->nullable();
            $table->foreignId('zip_id')->nullable()->constrained('zipcodes')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_shipping_addresses');
        Schema::dropIfExists('case_additional_info');
        Schema::table('cases', function (Blueprint $table) {
            $table->dropForeign(['scanner_id']);
            $table->dropColumn(['scanner_id', 'impression_method']);
        });
    }
};
