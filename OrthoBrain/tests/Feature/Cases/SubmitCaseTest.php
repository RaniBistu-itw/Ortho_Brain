<?php

use App\Support\ActivePractice;
use Illuminate\Support\Facades\DB;

// ─── Access control ─────────────────────────────────────────────────────────

it('rejects unauthenticated submit with 401', function () {
    $this->postJson('/dev/cases/1/submit')->assertStatus(401);
});

// ─── Prescription guard (existing — regression coverage) ─────────────────────

it('rejects submit when prescription is missing', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/submit")
        ->assertStatus(422)
        ->assertJsonPath('ok', false)
        ->assertJsonPath('errors.prescription', fn ($v) => str_contains($v, 'Prescription'));
});

// ─── Patient guard (new) ─────────────────────────────────────────────────────

it('rejects submit when patient_id is null even with a prescription', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    DB::table('prescriptions')->insert(['case_id' => $caseId]);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/submit")
        ->assertStatus(422)
        ->assertJson([
            'ok'    => false,
            'error' => 'patient_required',
        ])
        ->assertJsonPath('message', fn ($v) => str_contains($v, 'patient'));
});

it('allows submit when both prescription and patient are present', function () {
    ['practiceId' => $pid, 'practiceId' => $practiceId, 'doctorId' => $doctorId, 'caseId' => $caseId] = makeDoctorCase();

    $patientId = DB::table('patients')->insertGetId([
        'doctor_id'    => $doctorId,
        'practice_id'  => $practiceId,
        'first_name'   => 'Jane',
        'last_name'    => 'Smith',
        'date_of_birth' => '1990-01-01',
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    DB::table('cases')->where('id', $caseId)->update(['patient_id' => $patientId]);
    DB::table('prescriptions')->insert(['case_id' => $caseId]);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/submit")
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    $this->assertDatabaseHas('cases', [
        'id'     => $caseId,
        'status' => 'SUBMITTED',
    ]);
});

// ─── Isolation ──────────────────────────────────────────────────────────────

it('cannot submit another doctors case', function () {
    // Doctor A owns the case
    ['practiceId' => $pidA, 'caseId' => $caseId, 'doctorId' => $doctorId] = makeDoctorCase();
    $patientId = DB::table('patients')->insertGetId([
        'doctor_id'    => $doctorId,
        'practice_id'  => $pidA,
        'first_name'   => 'Pat',
        'last_name'    => 'Ient',
        'date_of_birth' => '1985-06-15',
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);
    DB::table('cases')->where('id', $caseId)->update(['patient_id' => $patientId]);
    DB::table('prescriptions')->insert(['case_id' => $caseId]);

    // Doctor B logs in
    ['practiceId' => $pidB] = makeDoctorCase();

    $this->withSession([ActivePractice::SESSION_KEY => $pidB])
        ->postJson("/dev/cases/{$caseId}/submit")
        ->assertStatus(404);
});
