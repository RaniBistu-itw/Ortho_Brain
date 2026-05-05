<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->enum('primary_address_source', ['PRACTICE', 'OTHER'])
                ->nullable()
                ->after('rejection_reason');

            $table->foreignId('primary_address_practice_id')
                ->nullable()
                ->after('primary_address_source')
                ->constrained('practices')
                ->nullOnDelete();

            $table->string('street_address_1', 255)->nullable()->after('primary_address_practice_id');
            $table->string('street_address_2', 255)->nullable()->after('street_address_1');
            $table->foreignId('zip_id')->nullable()->after('street_address_2')->constrained('zipcodes')->restrictOnDelete();
            $table->foreignId('city_id')->nullable()->after('zip_id')->constrained('cities')->restrictOnDelete();
            $table->foreignId('state_id')->nullable()->after('city_id')->constrained('states')->restrictOnDelete();
            $table->foreignId('country_id')->nullable()->after('state_id')->constrained('countries')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['zip_id']);
            $table->dropForeign(['primary_address_practice_id']);

            $table->dropColumn([
                'primary_address_source',
                'primary_address_practice_id',
                'street_address_1',
                'street_address_2',
                'zip_id',
                'city_id',
                'state_id',
                'country_id',
            ]);
        });
    }
};
