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

        // Pick first three ACTIVE practices: primary + two demo extras
        $practiceIds = Practice::where('status', 'ACTIVE')->orderBy('id')->limit(3)->pluck('id')->all();
        $primaryPracticeId = $practiceIds[0] ?? null;

        // 2. Create the doctor profile (clinical layer)
        $doctor = Doctor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'practice_id'                        => $primaryPracticeId,
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

        // 3. Multi-practice demo: primary APPROVED, second APPROVED, third PENDING.
        // Reset to a known shape so re-runs are deterministic.
        $doctor->practices()->detach();

        if (isset($practiceIds[0])) {
            $doctor->practices()->attach($practiceIds[0], [
                'approval_status' => 'APPROVED',
                'is_primary'      => true,
                'requested_at'    => now(),
                'approved_at'     => now(),
            ]);
        }
        if (isset($practiceIds[1])) {
            $doctor->practices()->attach($practiceIds[1], [
                'approval_status' => 'APPROVED',
                'is_primary'      => false,
                'requested_at'    => now(),
                'approved_at'     => now(),
            ]);
        }
        if (isset($practiceIds[2])) {
            $doctor->practices()->attach($practiceIds[2], [
                'approval_status' => 'PENDING',
                'is_primary'      => false,
                'requested_at'    => now(),
            ]);
        }
    }
}