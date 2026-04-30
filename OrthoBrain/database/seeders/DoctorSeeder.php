<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Practice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds all demo doctors in one place:
 *   - 1 primary "Test Doctor" with a 3-practice spread (APPROVED + APPROVED + PENDING)
 *     that drives the practice-pending banner / notification bell demo.
 *   - 11 additional demo doctors with portrait avatars pulled from pravatar.cc,
 *     each linked to the first ACTIVE practice as APPROVED + primary.
 *
 * All users share password `Password@1` (override via TEST_DOCTOR_PASSWORD).
 * Idempotent on re-run — pivots are reset to a known shape.
 */
class DoctorSeeder extends Seeder
{
    private const DEMO_FIRST_NAMES = [
        'Devansh', 'Kamlesh', 'Rani', 'Manish', 'Mukesh',
        'Prashant', 'Ruth', 'Rodriguez', 'Yasmine', 'Claudia', 'Jacob',
    ];

    private const DEMO_LAST_NAME_POOL = [
        'Sharma', 'Patel', 'Khan', 'Verma', 'Singh', 'Mehta',
        'Cohen', 'Levy', 'Goldberg',
        'Garcia', 'Lopez', 'Martinez', 'Hernandez',
        'Smith', 'Johnson', 'Wilson', 'Roberts', 'Cooper',
        'Hassan', 'Karimi', 'Rahman',
        'Nakamura', 'Tanaka',
    ];

    public function run(): void
    {
        $this->seedPrimaryTestDoctor();
        $this->seedDemoDoctors();
    }

    private function seedPrimaryTestDoctor(): void
    {
        $email    = env('TEST_DOCTOR_EMAIL', 'doctor@orthobrain.local');
        $password = env('TEST_DOCTOR_PASSWORD', 'Password@1');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'password_hash'     => $password,
                'role'              => 'DOCTOR',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $practiceIds       = Practice::where('status', 'ACTIVE')->orderBy('id')->limit(3)->pluck('id')->all();
        $primaryPracticeId = $practiceIds[0] ?? null;

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

        // Multi-practice demo: primary APPROVED, second APPROVED, third PENDING.
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

    private function seedDemoDoctors(): void
    {
        $practiceId = Practice::where('status', 'ACTIVE')->orderBy('id')->value('id');
        $usedLastNames = [];
        $rows = [];

        foreach (self::DEMO_FIRST_NAMES as $i => $firstName) {
            do {
                $lastName = self::DEMO_LAST_NAME_POOL[array_rand(self::DEMO_LAST_NAME_POOL)];
            } while (in_array($lastName, $usedLastNames, true) && count($usedLastNames) < count(self::DEMO_LAST_NAME_POOL));
            $usedLastNames[] = $lastName;

            $email = strtolower($firstName . '.' . $lastName) . '@orthobrain.local';

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'password_hash'     => 'Password@1',
                    'role'              => 'DOCTOR',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );

            $avatarKey = $this->fetchAvatar($firstName, $lastName, $i + 11);

            $doctor = Doctor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'practice_id'                        => $practiceId,
                    'first_name'                         => $firstName,
                    'last_name'                          => $lastName,
                    'profile_photo_s3_key'               => $avatarKey,
                    'preferred_language'                 => 'English',
                    'currently_providing_ortho_services' => true,
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

            if ($practiceId) {
                $doctor->practices()->detach();
                $doctor->practices()->attach($practiceId, [
                    'approval_status' => 'APPROVED',
                    'is_primary'      => true,
                    'requested_at'    => now(),
                    'approved_at'     => now(),
                ]);
            }

            $rows[] = "  $firstName $lastName  <$email>";
        }

        $this->command?->info("\nSeeded " . (count($rows) + 1) . " doctors (1 primary + " . count($rows) . " demo):");
        $this->command?->line("  Test Doctor  <" . env('TEST_DOCTOR_EMAIL', 'doctor@orthobrain.local') . ">  [multi-practice]");
        foreach ($rows as $line) {
            $this->command?->line($line);
        }
        $this->command?->info("\nAll use password: Password@1");
    }

    private function fetchAvatar(string $firstName, string $lastName, int $pravatarIndex): ?string
    {
        $slug = Str::slug($firstName . '-' . $lastName);
        $relativePath = "doctors/avatars/{$slug}.jpg";
        $url = 'https://i.pravatar.cc/300?img=' . $pravatarIndex;

        try {
            $context = stream_context_create(['http' => ['timeout' => 8]]);
            $bytes = @file_get_contents($url, false, $context);
            if ($bytes === false || strlen($bytes) < 1000) {
                $this->command?->warn("  avatar fetch failed for {$slug}, leaving null");
                return null;
            }
            Storage::disk('public')->put($relativePath, $bytes);
            return $relativePath;
        } catch (\Throwable $e) {
            $this->command?->warn("  avatar fetch error for {$slug}: " . $e->getMessage());
            return null;
        }
    }
}
