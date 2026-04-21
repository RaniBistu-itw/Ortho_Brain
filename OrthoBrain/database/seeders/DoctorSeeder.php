<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Practice;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('TEST_DOCTOR_EMAIL', 'doctor@orthobrain.local');
        $password = env('TEST_DOCTOR_PASSWORD', 'Password@1');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'password_hash' => $password,
                'role'          => 'DOCTOR',
                'is_active'     => true,
            ]
        );

        // Affiliate the test doctor with the first seeded practice (if any).
        $practiceId = Practice::where('status', 'ACTIVE')->orderBy('id')->value('id');

        // 2. Create the doctor profile (clinical layer)
        Doctor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'practice_id'                        => $practiceId,
                'first_name'                         => 'Test',
                'last_name'                          => 'Doctor',
                'preferred_language'                 => 'English',
                'currently_providing_ortho_services' => false,
                'preferred_contact_mode'             => 'DOCTOR_ONLY',
                'doctor_contact_email'               => $email,
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