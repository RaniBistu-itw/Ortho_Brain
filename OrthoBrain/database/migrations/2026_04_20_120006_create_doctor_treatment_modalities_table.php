<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_treatment_modalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('treatment_modality_id')->constrained('treatment_modalities')->onDelete('restrict');
            $table->timestamps();
            $table->unique(['doctor_id', 'treatment_modality_id'], 'doctor_treatment_modality_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_treatment_modalities');
    }
};
