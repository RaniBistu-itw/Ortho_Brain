<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->unique()->constrained('cases')->onDelete('cascade');

            $table->enum('arches', ['BOTH', 'MAXILLARY', 'MANDIBULAR'])->nullable();

            $table->boolean('ipr_enabled')->default(true);
            $table->enum('ipr_value', ['DEFER', 'NO_IPR', 'OTHER'])->nullable();

            $table->boolean('attachments_enabled')->default(true);
            $table->enum('attachments_value', ['STEP_1', 'SPECIFIC_STEP'])->nullable();
            $table->unsignedTinyInteger('attachments_specific_step')->nullable();

            $table->boolean('elastics_enabled')->default(true);
            $table->enum('elastics_value', ['YES', 'NO'])->nullable();

            $table->boolean('extractions_enabled')->default(true);
            $table->enum('extractions_value', ['YES', 'NO'])->nullable();

            $table->enum('tooth_movement_mode', ['NONE', 'SELECT'])->default('NONE');
            $table->enum('attachment_restrictions_mode', ['NONE', 'SELECT'])->default('NONE');

            $table->text('additional_comments')->nullable();

            $table->enum('future_restorative_work_has_work', ['YES', 'NO'])->nullable();
            $table->text('future_restorative_work_explanation')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
