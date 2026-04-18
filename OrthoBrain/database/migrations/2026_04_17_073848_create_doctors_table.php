<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('profile_photo_s3_key', 500)->nullable();
            $table->string('practice_name', 200);
            $table->enum('practice_phone_country_code', ['+1_US', '+1_CA', '+61_AU']);
            $table->string('practice_phone_number', 20);
            $table->string('practice_website');
            $table->string('preferred_language', 50);
            $table->boolean('currently_providing_ortho_services');
            $table->enum('preferred_contact_mode', ['DOCTOR_ONLY', 'EMPLOYEE_OFFICE', 'DOCTOR_AND_EMPLOYEE_OFFICE']);
            $table->string('doctor_contact_email', 150);
            $table->string('doctor_cell_phone', 20)->nullable();
            $table->string('other_email', 150)->nullable();
            $table->enum('preferred_tooth_numbering_system', ['UNIVERSAL', 'FDI', 'PALMER', 'INTERNATIONAL']);
            $table->enum('smile_arc_pref', ['DEFER', 'LATERALS_0_5MM_SHORTER', 'LATERALS_SAME_LENGTH']);
            $table->enum('small_lateral_incisors_pref', ['DEFER', 'IPR_LOWER_CAMOUFLAGE', 'LEAVE_SPACING_MESIAL_DISTAL']);
            $table->enum('mixed_dentition_pref', ['DEFER', 'NO_APPLIANCES']);
            $table->enum('orthodontic_extractions_pref', ['DEFER', 'NO_EXTRACTIONS']);
            $table->enum('ipr_protocol_pref', ['DEFER', 'NO_IPR', 'OTHER']);
            $table->text('ipr_protocol_other_note')->nullable();
            $table->enum('elastics_bonded_buttons_pref', ['YES', 'NO']);
            $table->enum('extractions_if_suggested_pref', ['YES', 'NO']);
            $table->enum('attachment_stage_pref', ['AT_STEP_1', 'AT_STEP_OTHER']);
            $table->enum('approval_status', ['PENDING', 'APPROVED', 'REJECTED', 'SUSPENDED'])->default('PENDING');
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('approved_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
