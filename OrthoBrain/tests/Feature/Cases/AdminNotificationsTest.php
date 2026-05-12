<?php

use App\Models\CaseModel;
use App\Models\User;
use App\Notifications\CaseApprovedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

// ─── Admin status transition notifications ───────────────────────────────────
//
// APPROVED and REJECTED transitions notify the doctor via database channel.
// All other transitions (→IN_REVIEW, self-transitions) are admin-internal
// and must NOT dispatch notifications.
// Sprint B-4a.

it('dispatches CaseApprovedNotification to the doctor on IN_REVIEW → APPROVED', function () {
    Notification::fake();

    ['caseId' => $caseId, 'user' => $doctorUser] = makeDoctorCase(['status' => 'IN_REVIEW']);

    $admin = User::factory()->admin()->create();
    test()->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/status", ['status' => 'APPROVED'])
        ->assertStatus(200)
        ->assertJson(['ok' => true, 'status' => 'APPROVED']);

    Notification::assertSentTo($doctorUser, CaseApprovedNotification::class);
});

it('does not dispatch any notification on APPROVED → IN_REVIEW transition', function () {
    Notification::fake();

    ['caseId' => $caseId] = makeDoctorCase(['status' => 'APPROVED']);

    $admin = User::factory()->admin()->create();
    test()->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/status", ['status' => 'IN_REVIEW'])
        ->assertStatus(200);

    Notification::assertNothingSent();
});

it('does not dispatch any notification on SUBMITTED → IN_REVIEW transition', function () {
    Notification::fake();

    ['caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $admin = User::factory()->admin()->create();
    test()->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/status", ['status' => 'IN_REVIEW'])
        ->assertStatus(200);

    Notification::assertNothingSent();
});

// ─── Notification body content ───────────────────────────────────────────────

it('includes case_code in approved notification body', function () {
    ['caseId' => $caseId, 'user' => $doctorUser] = makeDoctorCase(['case_code' => 'D1-A-099']);
    $case = CaseModel::find($caseId);

    $body = (new CaseApprovedNotification($case))->toArray($doctorUser)['body'];

    expect($body)->toContain('D1-A-099');
});

it('falls back to case id when case_code is null', function () {
    ['caseId' => $caseId, 'user' => $doctorUser] = makeDoctorCase();
    DB::table('cases')->where('id', $caseId)->update(['case_code' => null]);
    $case = CaseModel::find($caseId);

    $body = (new CaseApprovedNotification($case))->toArray($doctorUser)['body'];

    expect($body)->toContain('Case #' . $caseId);
});
