<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_buccal_corridors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('buccal_corridor_option_id')->constrained('buccal_corridor_options')->onDelete('restrict');
            $table->timestamps();
            $table->unique(['doctor_id', 'buccal_corridor_option_id'], 'doctor_buccal_corridor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_buccal_corridors');
    }
};
