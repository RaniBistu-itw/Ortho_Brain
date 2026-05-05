<?php

use App\Models\User;

// ─── Admin status dropdown — legal-transition filter ──────────────────────────
//
// The admin case-edit page renders a "Save Status" dropdown. Before this
// PR it listed all 5 statuses regardless of context, so an admin could
// pick an illegal target and only learn about the rejection from a
// generic "Failed to update status" alert. The fix filters the dropdown
// server-side to current_status + ALLOWED_TRANSITIONS[current_status].
// These tests lock that contract per starting status.

/**
 * Pull the option values out of the admin status <select>. Scoped to the
 * select with id="admin-status-select" so unrelated dropdowns on the
 * page (e.g. country, scanner) don't pollute the assertion.
 */
function extractStatusDropdownOptions(string $html): array
{
    preg_match('/<select[^>]*id="admin-status-select"[^>]*>(.*?)<\/select>/s', $html, $m);
    expect($m[1] ?? null)->not->toBeNull('admin-status-select not found in response');
    preg_match_all('/<option\s+value="([^"]*)"/', $m[1], $opts);
    return $opts[1];
}

function viewCaseAsAdmin(int $caseId): string
{
    $admin = User::factory()->admin()->create();
    test()->actingAs($admin);
    return test()->get("/admin/cases/{$caseId}/edit")
        ->assertStatus(200)
        ->getContent();
}

it('shows DRAFT + SUBMITTED for a DRAFT case', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'DRAFT']);
    expect(extractStatusDropdownOptions(viewCaseAsAdmin($caseId)))
        ->toEqualCanonicalizing(['DRAFT', 'SUBMITTED']);
});

it('shows SUBMITTED + IN_REVIEW for a SUBMITTED case', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);
    expect(extractStatusDropdownOptions(viewCaseAsAdmin($caseId)))
        ->toEqualCanonicalizing(['SUBMITTED', 'IN_REVIEW']);
});

it('shows IN_REVIEW + APPROVED + REJECTED for an IN_REVIEW case', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'IN_REVIEW']);
    expect(extractStatusDropdownOptions(viewCaseAsAdmin($caseId)))
        ->toEqualCanonicalizing(['IN_REVIEW', 'APPROVED', 'REJECTED']);
});

it('shows APPROVED + IN_REVIEW for an APPROVED case', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'APPROVED']);
    expect(extractStatusDropdownOptions(viewCaseAsAdmin($caseId)))
        ->toEqualCanonicalizing(['APPROVED', 'IN_REVIEW']);
});

it('shows REJECTED + IN_REVIEW for a REJECTED case', function () {
    ['caseId' => $caseId] = makeDoctorCase(['status' => 'REJECTED']);
    expect(extractStatusDropdownOptions(viewCaseAsAdmin($caseId)))
        ->toEqualCanonicalizing(['REJECTED', 'IN_REVIEW']);
});
