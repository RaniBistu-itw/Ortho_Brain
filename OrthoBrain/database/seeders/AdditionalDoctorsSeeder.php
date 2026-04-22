<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Practice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One-off seeder: adds 11 demo doctors with deterministic first names + random
 * last names + avatar portraits sourced from pravatar.cc. Not part of the
 * default seeder chain — invoke explicitly:
 *
 *     php artisan db:seed --class=AdditionalDoctorsSeeder
 */
class AdditionalDoctorsSeeder extends Seeder
{
    private const FIRST_NAMES = [
        'Devansh', 'Kamlesh', 'Rani', 'Manish', 'Mukesh',
        'Prashant', 'Ruth', 'Rodriguez', 'Yasmine', 'Claudia', 'Jacob',
    ];

    private const LAST_NAME_POOL = [
        'Sharma', 'Patel', 'Khan', 'Verma', 'Singh', 'Mehta',
        'Cohen', 'Levy', 'Goldberg',
        'Garcia', 'Lopez', 'Martinez', 'Hernandez',
        'Smith', 'Johnson', 'Wilson', 'Roberts', 'Cooper',
        'Hassan', 'Karimi', 'Rahman',
        'Nakamura', 'Tanaka',
    ];

    public function run(): void
    {
        $practiceId = Practice::where('status', 'ACTIVE')->orderBy('id')->value('id');
        $usedLastNames = [];
        $rows = [];

        foreach (self::FIRST_NAMES as $i => $firstName) {
            // Pick a unique random last name; reseeding is fine.
            do {
                $lastName = self::LAST_NAME_POOL[array_rand(self::LAST_NAME_POOL)];
            } while (in_array($lastName, $usedLastNames, true) && count($usedLastNames) < count(self::LAST_NAME_POOL));
            $usedLastNames[] = $lastName;

            $email = strtolower($firstName . '.' . $lastName) . '@orthobrain.local';

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'password_hash' => 'Password@1',
                    'role'          => 'DOCTOR',
                    'is_active'     => true,
                ]
            );

            $avatarKey = $this->fetchAvatar($firstName, $lastName, $i + 11);

            Doctor::updateOrCreate(
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

            $rows[] = "  $firstName $lastName  <$email>";
        }

        $this->command?->info("\nSeeded " . count($rows) . " demo doctors:");
        foreach ($rows as $line) {
            $this->command?->line($line);
        }
        $this->command?->info("\nAll use password: Password@1");
    }

    /**
     * Download a portrait avatar to storage/app/public/doctors/avatars/{slug}.jpg
     * and return the storage key. Falls back to null on network failure so the
     * seeder doesn't abort.
     */
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
