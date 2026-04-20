<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->enum('type', ['shipping', 'billing']);
            $table->string('street_address_1', 255);
            $table->string('street_address_2', 255)->nullable();
            $table->foreignId('zip_id')->constrained('zipcodes')->onDelete('restrict');
            $table->foreignId('city_id')->constrained('cities')->onDelete('restrict');
            $table->foreignId('state_id')->constrained('states')->onDelete('restrict');
            $table->foreignId('country_id')->constrained('countries')->onDelete('restrict');
            $table->string('billing_email', 150)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['doctor_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_addresses');
    }
};
