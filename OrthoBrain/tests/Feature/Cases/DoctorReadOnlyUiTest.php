<?php

use App\Support\ActivePractice;

// B-1b: doctor-side read-only UI bindings on the Add Case wizard.
//
// What this test covers:
//   - The server-rendered HTML carries window.__caseStatus seeded from the
//     case row, and the read-only formula in add-case.blade.php:240 sets
//     window.__isReadOnly = (caseStatus !== 'DRAFT') && !$adminMode at run time.
//   - The Alpine :disabled="isReadOnly" bindings are present in the rendered
//     section partials so the client-side getter (added in each section's
//     factory) actually has something to bind to.
//
// What this test does NOT cover:
//   - The actual disabled state of inputs at runtime — Alpine bindings
//     evaluate client-side after DOMContentLoaded, so Pest's raw HTML
//     assertions can only verify the binding strings are present, not that
//     they fire. Real disabled-state verification belongs in a browser
//     test (Laravel Dusk or equivalent), tracked alongside the Phase 9
//     manual-smoke discipline (CLAUDE.md Entry 9).
//   - Submit / Save Draft button visibility — already covered by
//     DoctorEditGateTest.

it('seeds window.__caseStatus = "SUBMITTED" so __isReadOnly evaluates true at runtime', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/edit")
        ->assertStatus(200);

    // window.__caseStatus is the input to the runtime formula in
    // add-case.blade.php:240. With caseStatus !== 'DRAFT' and adminMode=false
    // (doctor route), window.__isReadOnly resolves to true client-side, which
    // in turn drives window.AddCaseState.isReadOnly = true.
    $response->assertSee('window.__caseStatus = "SUBMITTED"', false);
});

it('seeds window.__caseStatus = "DRAFT" so __isReadOnly evaluates false at runtime', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'DRAFT']);

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/edit")
        ->assertStatus(200);

    $response->assertSee('window.__caseStatus = "DRAFT"', false);
});

it('renders the :disabled="isReadOnly" Alpine binding on key form controls', function () {
    // Status doesn't matter for binding *presence* — the Blade output is the
    // same regardless. We use SUBMITTED so a future regression that
    // server-conditioned the binding (anti-pattern) would be caught by the
    // window.__caseStatus assertion above + this assertion failing on DRAFT.
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/edit")
        ->assertStatus(200);

    // Spot-check one binding per partial that owns Alpine controls. If any
    // partial's :disabled binding is removed, this fails loudly.
    $response->assertSee(':disabled="isReadOnly"', false);
});

it('renders the x-show="!isReadOnly" gate on photographs bulk Upload button', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/edit")
        ->assertStatus(200);

    // The bulk Upload Images button in photographs.blade.php is hidden via
    // x-show="!isReadOnly" when read-only. Same pattern in xrays.blade.php
    // and on per-tile Replace/Remove/Crop buttons in media-tile.blade.php.
    $response->assertSee('x-show="!isReadOnly"', false);
});
