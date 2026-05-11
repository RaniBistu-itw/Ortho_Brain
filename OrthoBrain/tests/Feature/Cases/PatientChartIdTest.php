<?php

use App\Support\ActivePractice;
use Illuminate\Support\Facades\DB;

// ─── Patient chart_id auto-generation ────────────────────────────────────────
//
// PatientController::upsert() auto-generates chart_id on creation when the
// doctor does not supply one. Format: PT-{doctor_id}-{practice_id}-{patient_id}
// On update, an existing chart_id is never clobbered by a null payload.

$validPayload = fn () => [
    'firstName'        => 'Jane',
    'lastName'         => 'Smith',
    'dateOfBirth'      => '1990-06-15',
    'biologicalGender' => 'Female',
    'chiefComplaint'   => 'Crowding on upper arch.',
];

it('auto-generates chart_id on patient creation when none provided', function () use ($validPayload) {
    ['practiceId' => $pid, 'doctorId' => $doctorId, 'caseId' => $caseId] = makeDoctorCase();

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/patient", $validPayload())
        ->assertStatus(200)
        ->assertJsonPath('ok', true);

    $patientId = DB::table('cases')->where('id', $caseId)->value('patient_id');
    $chartId   = DB::table('patients')->where('id', $patientId)->value('chart_id');

    expect($chartId)->toBe("PT-{$doctorId}-{$pid}-{$patientId}");
});

it('returns the auto-generated chart_id in the response', function () use ($validPayload) {
    ['practiceId' => $pid, 'doctorId' => $doctorId, 'caseId' => $caseId] = makeDoctorCase();

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/patient", $validPayload())
        ->assertStatus(200)
        ->json();

    $patientId = $response['patient']['id'];
    expect($response['patient']['chartId'])->toBe("PT-{$doctorId}-{$pid}-{$patientId}");
});

it('does not overwrite existing chart_id when update omits patientChartId', function () use ($validPayload) {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    // First save — creates patient and auto-generates chart_id.
    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/patient", $validPayload())
        ->assertStatus(200);

    $patientId     = DB::table('cases')->where('id', $caseId)->value('patient_id');
    $originalChart = DB::table('patients')->where('id', $patientId)->value('chart_id');

    // Second save — no patientChartId in payload.
    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/patient", $validPayload())
        ->assertStatus(200);

    $chartAfterUpdate = DB::table('patients')->where('id', $patientId)->value('chart_id');

    expect($chartAfterUpdate)->toBe($originalChart);
});

it('respects a manually provided patientChartId and does not overwrite it', function () use ($validPayload) {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/patient", array_merge($validPayload(), [
            'patientChartId' => 'MANUAL-001',
        ]))
        ->assertStatus(200);

    $patientId = DB::table('cases')->where('id', $caseId)->value('patient_id');
    $chartId   = DB::table('patients')->where('id', $patientId)->value('chart_id');

    expect($chartId)->toBe('MANUAL-001');
});
