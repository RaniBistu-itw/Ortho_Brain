<?php

use App\Models\User;
use App\Notifications\CaseRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

// ─── Admin rejection flow — required reason on REJECTED transitions ─────────
//
// IN_REVIEW → REJECTED requires a rejection_reason (min 10, max 2000 chars).
// REJECTED → IN_REVIEW clears the prior reason. Other transitions ignore the
// reason field entirely. Sprint B-3.

function rejectCaseAs(int $caseId, array $body): \Illuminate\Testing\TestResponse
{
    $admin = User::factory()->admin()->create();
    return test()
        ->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/status", $body);
}

it('accepts REJECTED transition with valid rejection_reason and persists it', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'IN_REVIEW']);

    $reason = 'Insufficient photo quality, please retake the lateral views.';
    rejectCaseAs($caseId, ['status' => 'REJECTED', 'rejection_reason' => $reason])
        ->assertStatus(200)
        ->assertJson(['ok' => true, 'status' => 'REJECTED']);

    $row = DB::table('cases')->where('id', $caseId)->first();
    expect($row->status)->toBe('REJECTED');
    expect($row->rejection_reason)->toBe($reason);
});

it('rejects REJECTED transition with no rejection_reason (422)', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'IN_REVIEW']);

    rejectCaseAs($caseId, ['status' => 'REJECTED'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['rejection_reason']);
});

it('rejects REJECTED transition with rejection_reason shorter than 10 chars (422)', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'IN_REVIEW']);

    rejectCaseAs($caseId, ['status' => 'REJECTED', 'rejection_reason' => 'too short'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['rejection_reason']);
});

it('rejects REJECTED transition with rejection_reason longer than 2000 chars (422)', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'IN_REVIEW']);

    rejectCaseAs($caseId, ['status' => 'REJECTED', 'rejection_reason' => str_repeat('x', 2001)])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['rejection_reason']);
});

it('ignores rejection_reason on non-REJECTED transitions', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'IN_REVIEW']);

    rejectCaseAs($caseId, ['status' => 'APPROVED', 'rejection_reason' => 'ignored — not a rejection'])
        ->assertStatus(200);

    $row = DB::table('cases')->where('id', $caseId)->first();
    expect($row->status)->toBe('APPROVED');
    expect($row->rejection_reason)->toBeNull();
});

it('allows REJECTED → IN_REVIEW transition without rejection_reason', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'REJECTED']);
    DB::table('cases')->where('id', $caseId)->update(['rejection_reason' => 'prior reason text here']);

    rejectCaseAs($caseId, ['status' => 'IN_REVIEW'])
        ->assertStatus(200)
        ->assertJson(['ok' => true, 'status' => 'IN_REVIEW']);
});

it('clears rejection_reason on REJECTED → IN_REVIEW transition', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'REJECTED']);
    DB::table('cases')->where('id', $caseId)->update(['rejection_reason' => 'prior reason text here']);

    rejectCaseAs($caseId, ['status' => 'IN_REVIEW'])->assertStatus(200);

    expect(DB::table('cases')->where('id', $caseId)->value('rejection_reason'))->toBeNull();
});

it('overwrites rejection_reason on second rejection (IN_REVIEW → REJECTED with new reason)', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'IN_REVIEW']);
    DB::table('cases')->where('id', $caseId)->update(['rejection_reason' => 'prior reason from earlier']);

    $newReason = 'New reason after admin reopened and re-reviewed the case.';
    rejectCaseAs($caseId, ['status' => 'REJECTED', 'rejection_reason' => $newReason])
        ->assertStatus(200);

    expect(DB::table('cases')->where('id', $caseId)->value('rejection_reason'))->toBe($newReason);
});

it('dispatches CaseRejectedNotification with reason on IN_REVIEW → REJECTED transition', function () {
    Notification::fake();

    ['caseId' => $caseId, 'user' => $doctorUser] = makeDoctorCase(['status' => 'IN_REVIEW']);

    $reason = 'Photos are out of focus and unusable for treatment planning.';
    $admin = User::factory()->admin()->create();
    test()->actingAs($admin)
        ->postJson("/admin/cases/{$caseId}/status", [
            'status'           => 'REJECTED',
            'rejection_reason' => $reason,
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    Notification::assertSentTo($doctorUser, CaseRejectedNotification::class);
});

it('does not dispatch any notification on IN_REVIEW → IN_REVIEW self-transition no-op', function () {
    Notification::fake();

    ['caseId' => $caseId, 'user' => $doctorUser] = makeDoctorCase(['status' => 'IN_REVIEW']);

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
