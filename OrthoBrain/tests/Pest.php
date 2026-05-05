<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// Unit tests get the Laravel TestCase (so facades + config() work) but NOT
// RefreshDatabase — nothing in Unit/ should need the database.
pest()->extend(TestCase::class)
    ->in('Unit');

function loginAsAdmin(): User
{
    $user = User::factory()->admin()->create();
    test()->actingAs($user);
    return $user;
}

function loginAsDoctor(): User
{
    $user = User::factory()->create(['role' => 'DOCTOR', 'is_active' => true]);
    test()->actingAs($user);
    return $user;
}

/**
 * Creates a fully-wired doctor + practice + case and logs in as that doctor.
 * Sets up the doctor_practice pivot (APPROVED, is_primary) so the
 * EnsureActivePractice middleware is satisfied.
 *
 * Returns ['user', 'doctorId', 'practiceId', 'caseId'].
 * Use withSession([ActivePractice::SESSION_KEY => $practiceId]) on requests
 * so currentPractice() resolves without a real browser session.
 */
function makeDoctorCase(array $caseOverrides = []): array
{
    $user = User::factory()->create(['role' => 'DOCTOR', 'is_active' => true]);
    test()->actingAs($user);

    $now = now()->toDateTimeString();

    $practiceId = DB::table('practices')->insertGetId([
        'name'       => 'Test Practice',
        'status'     => 'ACTIVE',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $doctorId = DB::table('doctors')->insertGetId([
        'user_id'                            => $user->id,
        'first_name'                         => 'Test',
        'last_name'                          => 'Doctor',
        'preferred_language'                 => 'en',
        'currently_providing_ortho_services' => 1,
        'preferred_contact_mode'             => 'DOCTOR_ONLY',
        'doctor_contact_email'               => $user->email,
        'preferred_tooth_numbering_system'   => 'UNIVERSAL',
        'smile_arc_pref'                     => 'DEFER',
        'small_lateral_incisors_pref'        => 'DEFER',
        'mixed_dentition_pref'               => 'DEFER',
        'orthodontic_extractions_pref'       => 'DEFER',
        'ipr_protocol_pref'                  => 'DEFER',
        'elastics_bonded_buttons_pref'       => 'YES',
        'extractions_if_suggested_pref'      => 'YES',
        'attachment_stage_pref'              => 'AT_STEP_1',
        'approval_status'                    => 'APPROVED',
        'created_at'                         => $now,
        'updated_at'                         => $now,
    ]);

    DB::table('doctor_practice')->insert([
        'doctor_id'       => $doctorId,
        'practice_id'     => $practiceId,
        'approval_status' => 'APPROVED',
        'is_primary'      => true,
        'created_at'      => $now,
        'updated_at'      => $now,
    ]);

    $caseId = DB::table('cases')->insertGetId(array_merge([
        'doctor_id'   => $doctorId,
        'practice_id' => $practiceId,
        'status'      => 'DRAFT',
        'created_at'  => $now,
        'updated_at'  => $now,
    ], $caseOverrides));

    return [
        'user'       => $user,
        'doctorId'   => $doctorId,
        'practiceId' => $practiceId,
        'caseId'     => $caseId,
    ];
}
