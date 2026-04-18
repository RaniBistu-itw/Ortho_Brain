<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zipcodes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->text('details')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['city_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zipcodes');
    }
};
