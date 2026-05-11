<?php

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// B-2: admin view-only enforcement + section-save endpoints.
//
// What this covers:
//   - window.__isReadOnly formula widened for admin: APPROVED/REJECTED are
//     read-only; SUBMITTED/IN_REVIEW are editable. Formula is JS-evaluated
//     client-side — tests assert the seeded __caseStatus value and the
//     formula shape in the HTML, not the evaluated boolean.
//   - window.CASE_ADMIN_MODE = true seeded on every admin edit page
//     (precondition for the client-side pi-first-name lock in
//     patient-information.js — actual disabled state is JS-driven, not
//     server-rendered, so Pest can only assert the precondition).
//   - 5 admin section-save endpoints (shipping, impressions, additional,
//     patient, submit-order) return 200 + {ok: true} for SUBMITTED cases.
//
// What is NOT covered here:
//   - Alpine :disabled state at runtime — browser test only (Entry 9).
//   - pi-first-name disabled attribute in the DOM — JS-driven (Entry 9).

// ─── Admin __isReadOnly formula ───────────────────────────────────────────────

it('seeds CASE_ADMIN_MODE = true on admin edit page', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);
    $admin = User::factory()->admin()->create();
    $response = $this->actingAs($admin)
        ->get("/admin/cases/{$caseId}/edit")
        ->assertStatus(200);

    $response->assertSee('window.CASE_ADMIN_MODE = true', false);
});

it('uses the widened __isReadOnly formula (CASE_ADMIN_MODE branch) not the old !adminMode form', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);
    $admin = User::factory()->admin()->create();
    $response = $this->actingAs($admin)
        ->get("/admin/cases/{$caseId}/edit")
        ->assertStatus(200);

    // New formula shape — presence proves the B-2 change is in place.
    $response->assertSee('window.__isReadOnly = window.CASE_ADMIN_MODE', false);
    // Old formula shape must NOT appear (it hardcoded !adminMode = false for admin).
    $response->assertDontSee("window.__caseStatus !== 'DRAFT' && !true", false);
});

it('seeds window.__caseStatus = "SUBMITTED" for an admin viewing a SUBMITTED case', function () {
    // With the widened formula, CASE_ADMIN_MODE=true + __caseStatus="SUBMITTED"
    // means __isReadOnly evaluates to false client-side → form is editable.
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);
    $admin = User::factory()->admin()->create();
    $response = $this->actingAs($admin)
        ->get("/admin/cases/{$caseId}/edit")
        ->assertStatus(200);

    $response->assertSee('window.__caseStatus = "SUBMITTED"', false);
});

it('seeds window.__caseStatus = "APPROVED" for an admin viewing an APPROVED case', function () {
    // With the widened formula, CASE_ADMIN_MODE=true + __caseStatus="APPROVED"
    // means __isReadOnly evaluates to true client-side → form is read-only.
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'APPROVED']);
    $admin = User::factory()->admin()->create();
    $response = $this->actingAs($admin)
        ->get("/admin/cases/{$caseId}/edit")
        ->assertStatus(200);

    $response->assertSee('window.__caseStatus = "APPROVED"', false);
});

it('seeds window.__caseStatus = "REJECTED" for an admin viewing a REJECTED case', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'REJECTED']);
    $admin = User::factory()->admin()->create();
    $response = $this->actingAs($admin)
        ->get("/admin/cases/{$caseId}/edit")
        ->assertStatus(200);

    $response->assertSee('window.__caseStatus = "REJECTED"', false);
});

// ─── Admin section-save routes ────────────────────────────────────────────────

it('saves shipping address via admin route', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/shipping", [
            'practice'       => 'Test Practice',
            'doctorName'     => 'Dr. Test',
            'streetAddress'  => '123 Main St',
            'streetAddress2' => '',
            'zipId'          => null,
            'cityId'         => null,
            'stateId'        => null,
            'countryId'      => null,
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);
});

it('saves impressions via admin route', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/impressions", [
            'impressionMethod' => 'physical',
            'scannerId'        => null,
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    expect(DB::table('cases')->where('id', $caseId)->value('impression_method'))
        ->toBe('PHYSICAL');
});

it('saves additional info via admin route', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/additional", [
            'diagnosis' => ['crowding' => true],
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);
});

it('saves patient non-identity fields via admin route and does not change first_name', function () {
    ['caseId' => $caseId, 'doctorId' => $doctorId, 'practiceId' => $practiceId]
        = makeDoctorCase(['status' => 'SUBMITTED']);

    $now = now()->toDateTimeString();
    $patientId = DB::table('patients')->insertGetId([
        'doctor_id'         => $doctorId,
        'practice_id'       => $practiceId,
        'first_name'        => 'OriginalFirst',
        'last_name'         => 'OriginalLast',
        'date_of_birth'     => '1990-01-01',
        'biological_gender' => 'Male',
        'chief_complaint'   => 'Original complaint',
        'created_at'        => $now,
        'updated_at'        => $now,
    ]);
    DB::table('cases')->where('id', $caseId)->update(['patient_id' => $patientId]);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/patient", [
            'biologicalGender' => 'Female',
            'chiefComplaint'   => 'Updated complaint',
            'patientChartId'   => 'CHART-99',
            'email'            => 'test@example.com',
            'phone'            => '555-0100',
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    $patient = DB::table('patients')->where('id', $patientId)->first();
    expect($patient->biological_gender)->toBe('Female');
    expect($patient->chief_complaint)->toBe('Updated complaint');
    expect($patient->chart_id)->toBe('CHART-99');
    // Identity fields must not change.
    expect($patient->first_name)->toBe('OriginalFirst');
    expect($patient->last_name)->toBe('OriginalLast');
    expect($patient->date_of_birth)->toBe('1990-01-01');
});

it('saves submitter initials via admin route', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/submit-order", [
            'submitterInitials' => 'AB',
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    expect(DB::table('cases')->where('id', $caseId)->value('submitter_initials'))
        ->toBe('AB');
});
