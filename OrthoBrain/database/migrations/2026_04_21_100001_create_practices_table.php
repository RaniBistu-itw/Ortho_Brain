<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('name', 200);

            // Contact
            $table->string('website', 255)->nullable();
            $table->string('phone_country_code', 10)->nullable();
            $table->string('phone_number', 30)->nullable();

            // Physical address (same pattern as doctor_addresses)
            $table->string('street_address_1', 255)->nullable();
            $table->string('street_address_2', 255)->nullable();
            $table->foreignId('zip_id')->nullable()->constrained('zipcodes')->restrictOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->restrictOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('states')->restrictOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->restrictOnDelete();

            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index(['owner_id', 'status']);
            $table->index('zip_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practices');
    }
};
