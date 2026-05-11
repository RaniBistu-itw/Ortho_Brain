<?php

use App\Models\User;
use App\Notifications\CaseEditedByAdminNotification;
use Illuminate\Support\Facades\Notification;

// ─── Admin section-save edit notifications ───────────────────────────────────
//
// Every admin section-save endpoint dispatches CaseEditedByAdminNotification
// to the case's doctor. Doctor-side section-saves must NOT dispatch it.
// Sprint B-4b.

it('dispatches CaseEditedByAdminNotification on admin saveShipping', function () {
    Notification::fake();

    ['caseId' => $caseId, 'user' => $doctorUser] = makeDoctorCase(['status' => 'SUBMITTED']);

    $admin = User::factory()->admin()->create();
    test()->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/shipping", [
            'practice'       => 'Test Practice',
            'doctorName'     => 'Dr. Test',
            'streetAddress'  => '123 Main St',
            'streetAddress2' => null,
            'zipId'          => null,
            'cityId'         => null,
            'stateId'        => null,
            'countryId'      => null,
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    Notification::assertSentTo($doctorUser, CaseEditedByAdminNotification::class);
});

it('dispatches CaseEditedByAdminNotification on admin saveImpressions', function () {
    Notification::fake();

    ['caseId' => $caseId, 'user' => $doctorUser] = makeDoctorCase(['status' => 'SUBMITTED']);

    $admin = User::factory()->admin()->create();
    test()->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/impressions", [
            'impressionMethod' => 'digital',
            'scannerId'        => null,
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    Notification::assertSentTo($doctorUser, CaseEditedByAdminNotification::class);
});

it('dispatches CaseEditedByAdminNotification on admin saveAdditionalInfo', function () {
    Notification::fake();

    ['caseId' => $caseId, 'user' => $doctorUser] = makeDoctorCase(['status' => 'SUBMITTED']);

    $admin = User::factory()->admin()->create();
    test()->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/additional", [])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    Notification::assertSentTo($doctorUser, CaseEditedByAdminNotification::class);
});

it('dispatches CaseEditedByAdminNotification on admin savePatient', function () {
    Notification::fake();

    ['caseId' => $caseId, 'user' => $doctorUser] = makeDoctorCase(['status' => 'SUBMITTED']);

    $admin = User::factory()->admin()->create();
    test()->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/patient", [
            'biologicalGender' => 'MALE',
            'chiefComplaint'   => 'Crowding',
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    Notification::assertSentTo($doctorUser, CaseEditedByAdminNotification::class);
});

it('dispatches CaseEditedByAdminNotification on admin saveSubmitOrder', function () {
    Notification::fake();

    ['caseId' => $caseId, 'user' => $doctorUser] = makeDoctorCase(['status' => 'SUBMITTED']);

    $admin = User::factory()->admin()->create();
    test()->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/submit-order", [
            'submitterInitials' => 'AB',
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    Notification::assertSentTo($doctorUser, CaseEditedByAdminNotification::class);
});

it('does not dispatch CaseEditedByAdminNotification on doctor saveShipping', function () {
    Notification::fake();

    // Doctor-side saves must never fire the admin edit notification.
    // DRAFT status required so abortIfNotDraft does not block the request.
    ['caseId' => $caseId, 'practiceId' => $practiceId] = makeDoctorCase(['status' => 'DRAFT']);

    test()
        ->withSession(['active_practice_id' => $practiceId])
        ->postJson("/dev/cases/{$caseId}/shipping", [
            'practice'       => 'Test Practice',
            'doctorName'     => 'Dr. Test',
            'streetAddress'  => '123 Main St',
            'streetAddress2' => null,
            'zipId'          => null,
            'cityId'         => null,
            'stateId'        => null,
            'countryId'      => null,
        ])
        ->assertStatus(200);

    Notification::assertNothingSent();
});
