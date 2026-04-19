<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the user account (auth layer)
        // Note: password_hash cast is 'hashed' in User model, so we pass plain text
        // and Laravel hashes it automatically on save.
        $user = User::updateOrCreate(
            ['email' => 'doctor@orthobrain.test'],
            [
                'password_hash' => 'password123',
                'role'          => 'DOCTOR',
                'is_active'     => true,
            ]
        );

        // 2. Create the doctor profile (clinical layer)
        Doctor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name'                         => 'Test',
                'last_name'                          => 'Doctor',
                'practice_name'                      => 'Demo Dental Practice',
                'practice_phone_country_code'        => '+1_US',
                'practice_phone_number'              => '5551234567',
                'practice_website'                   => 'https://demo.example.com',
                'preferred_language'                 => 'English',
                'currently_providing_ortho_services' => false,
                'preferred_contact_mode'             => 'DOCTOR_ONLY',
                'doctor_contact_email'               => 'doctor@orthobrain.test',
                'preferred_tooth_numbering_system'   => 'UNIVERSAL',
                'smile_arc_pref'                     => 'DEFER',
                'small_lateral_incisors_pref'        => 'DEFER',
                'mixed_dentition_pref'               => 'DEFER',
                'orthodontic_extractions_pref'       => 'DEFER',
                'ipr_protocol_pref'                  => 'DEFER',
                'elastics_bonded_buttons_pref'       => 'NO',
                'extractions_if_suggested_pref'      => 'NO',
                'attachment_stage_pref'              => 'AT_STEP_1',
                'approval_status'                    => 'APPROVED',
                'approved_at'                        => now(),
            ]
        );
    }
}