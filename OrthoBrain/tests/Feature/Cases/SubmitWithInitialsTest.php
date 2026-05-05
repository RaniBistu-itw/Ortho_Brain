<?php

use App\Models\User;
use App\Support\ActivePractice;
use Illuminate\Support\Facades\DB;

// ─── Submitter initials — the third gate on submit() ──────────────────────────
//
// After the prescription gate (PR #95-era) and the patient_id gate (PR #96),
// submit() now requires submitter_initials. Stored as-typed (no uppercasing).

/**
 * Build a case that satisfies the prescription and patient_id submit gates so
 * the only thing left between us and a 200 is the initials check.
 */
function caseReadyForInitialsGate(): array
{
    $bundle = makeDoctorCase();

    $patientId = DB::table('patients')->insertGetId([
        'doctor_id'     => $bundle['doctorId'],
        'practice_id'   => $bundle['practiceId'],
        'first_name'    => 'Test',
        'last_name'     => 'Patient',
        'date_of_birth' => '1990-01-01',
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    DB::table('prescriptions')->insert(['case_id' => $bundle['caseId']]);
    DB::table('cases')->where('id', $bundle['caseId'])->update(['patient_id' => $patientId]);

    return $bundle;
}

function submitWith(int $caseId, int $practiceId, ?string $initials)
{
    $body = $initials === null ? [] : ['submitter_initials' => $initials];
    return test()
        ->withSession([ActivePractice::SESSION_KEY => $practiceId])
        ->postJson("/dev/cases/{$caseId}/submit", $body);
}

it('rejects submit when submitter_initials field is missing', function () {
    ['caseId' => $id, 'practiceId' => $pid] = caseReadyForInitialsGate();
    submitWith($id, $pid, null)
        ->assertStatus(422)
        ->assertJson(['ok' => false, 'error' => 'initials_required']);
});

it('rejects submit when submitter_initials is empty string', function () {
    ['caseId' => $id, 'practiceId' => $pid] = caseReadyForInitialsGate();
    submitWith($id, $pid, '')
        ->assertStatus(422)
        ->assertJson(['error' => 'initials_required']);
});

it('rejects submit with 1-character initials (below min)', function () {
    ['caseId' => $id, 'practiceId' => $pid] = caseReadyForInitialsGate();
    submitWith($id, $pid, 'A')->assertStatus(422);
});

it('rejects submit with 6-character initials (above max — server enforces 5)', function () {
    ['caseId' => $id, 'practiceId' => $pid] = caseReadyForInitialsGate();
    submitWith($id, $pid, 'ABCDEF')->assertStatus(422);
});

it('rejects submit with non-letter characters in initials', function () {
    ['caseId' => $id, 'practiceId' => $pid] = caseReadyForInitialsGate();
    submitWith($id, $pid, 'B1K')->assertStatus(422);
});

it('accepts valid initials and persists them as typed', function () {
    ['caseId' => $id, 'practiceId' => $pid] = caseReadyForInitialsGate();
    submitWith($id, $pid, 'BKL')
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    expect(DB::table('cases')->where('id', $id)->value('submitter_initials'))->toBe('BKL');
    expect(DB::table('cases')->where('id', $id)->value('status'))->toBe('SUBMITTED');
});

it('preserves mixed-case initials without normalising', function () {
    // Help text says "2-5 letters only (e.g. JD, ABcd)" — mixed case
    // is explicitly allowed and must be preserved. Normalizing to
    // uppercase violates the stated contract.
    ['caseId' => $id, 'practiceId' => $pid] = caseReadyForInitialsGate();
    submitWith($id, $pid, 'ABcd')
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    expect(DB::table('cases')->where('id', $id)->value('submitter_initials'))->toBe('ABcd');
});

// ─── Prefill round-trip — submitter initials reach the edit view ─────────────

function extractSubmitOrderPrefill(string $html): ?array
{
    preg_match('/window\.__submitOrderPrefill\s*=\s*([^;]+);/', $html, $m);
    expect($m[1] ?? null)->not->toBeNull('submitOrderPrefill global not found');
    $decoded = json_decode(trim($m[1]), true);
    return is_array($decoded) ? $decoded : null;
}

it('exposes submitter_initials in the doctor edit prefill', function () {
    $bundle = makeDoctorCase();
    DB::table('cases')->where('id', $bundle['caseId'])->update(['submitter_initials' => 'JDx']);

    $html = test()
        ->withSession([ActivePractice::SESSION_KEY => $bundle['practiceId']])
        ->get("/dev/cases/{$bundle['caseId']}/edit")
        ->assertStatus(200)
        ->getContent();

    expect(extractSubmitOrderPrefill($html))->toMatchArray(['submitterInitials' => 'JDx']);
});

it('exposes submitter_initials in the admin edit prefill (Entry 2 parity)', function () {
    ['caseId' => $caseId] = makeDoctorCase();
    DB::table('cases')->where('id', $caseId)->update(['submitter_initials' => 'AB']);

    $admin = User::factory()->admin()->create();
    test()->actingAs($admin);

    $html = test()->get("/admin/cases/{$caseId}/edit")
        ->assertStatus(200)
        ->getContent();

    expect(extractSubmitOrderPrefill($html))->toMatchArray(['submitterInitials' => 'AB']);
});
