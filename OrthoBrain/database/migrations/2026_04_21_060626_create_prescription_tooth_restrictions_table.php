<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_tooth_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->onDelete('cascade');
            $table->enum('restriction_type', ['MOVEMENT', 'ATTACHMENT']);
            $table->string('tooth_code', 4);
            $table->timestamps();
            $table->unique(['prescription_id', 'restriction_type', 'tooth_code'], 'uq_prescription_tooth');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_tooth_restrictions');
    }
};
