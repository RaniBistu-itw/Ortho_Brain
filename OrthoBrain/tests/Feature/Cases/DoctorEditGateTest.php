<?php

use App\Support\ActivePractice;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

// ─── Read endpoints — GET /cases/{id}/edit allowed at any status ────────────

it('allows GET /cases/{id}/edit on a DRAFT case (Submit button present)', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'DRAFT']);

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/edit")
        ->assertStatus(200);

    $response->assertSee('id="btn-submit"', false);
    $response->assertSee('id="btn-save-draft"', false);
});

it('allows GET /cases/{id}/edit on a SUBMITTED case (read-only — Submit + Save Draft absent)', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/edit")
        ->assertStatus(200);

    $response->assertDontSee('id="btn-submit"', false);
    $response->assertDontSee('id="btn-save-draft"', false);
});

it('allows GET /cases/{id}/edit on an APPROVED case (gate is universal for read)', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'APPROVED']);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/edit")
        ->assertStatus(200);
});

// ─── Write endpoints — 403 on SUBMITTED for all 8 gated methods ─────────────

it('403s saveShipping on a SUBMITTED case', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/shipping", ['streetAddress' => '123 Main St'])
        ->assertStatus(403);
});

it('403s saveImpressions on a SUBMITTED case', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/impressions", ['impressionMethod' => 'scan'])
        ->assertStatus(403);
});

it('403s saveAdditionalInfo on a SUBMITTED case', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/additional", [])
        ->assertStatus(403);
});

it('403s prescription update on a SUBMITTED case', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/prescription", [])
        ->assertStatus(403);
});

it('403s patient upsert on a SUBMITTED case', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/patient", [
            'firstName'        => 'Test',
            'lastName'         => 'Patient',
            'dateOfBirth'      => '1990-01-01',
            'biologicalGender' => 'Male',
            'chiefComplaint'   => 'Test',
        ])
        ->assertStatus(403);
});

it('403s media upload on a SUBMITTED case', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->post("/dev/cases/{$caseId}/media/upload", [
            'section' => 'photograph',
            'tile_id' => 'frontal-smile',
            'file'    => UploadedFile::fake()->image('test.jpg'),
        ])
        ->assertStatus(403);
});

it('403s media destroy on a SUBMITTED case', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/media/photograph/frontal-smile/destroy")
        ->assertStatus(403);
});

it('403s media reorder on a SUBMITTED case', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => 'SUBMITTED']);

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/media/reorder", [
            'section'        => 'photograph',
            'source_tile_id' => 'frontal-smile',
            'target_tile_id' => 'profile',
        ])
        ->assertStatus(403);
});

// ─── Gate is universal across non-DRAFT statuses ────────────────────────────

it('403s saveShipping on APPROVED, IN_REVIEW, REJECTED cases', function () {
    foreach (['APPROVED', 'IN_REVIEW', 'REJECTED'] as $status) {
        ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase(['status' => $status]);

        $this->withSession([ActivePractice::SESSION_KEY => $pid])
            ->postJson("/dev/cases/{$caseId}/shipping", ['streetAddress' => '123 Main St'])
            ->assertStatus(403, "Expected 403 for status={$status}");
    }
});
