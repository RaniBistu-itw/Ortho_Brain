<?php

use App\Support\ActivePractice;
use Illuminate\Support\Facades\DB;

// ─── Shipping endpoint ────────────────────────────────────────────────────────

it('persists shipping address with a valid integer zip_id', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    $zipId  = DB::table('zipcodes')->where('status', 'ACTIVE')->value('id');
    $cityId = DB::table('zipcodes')->where('id', $zipId)->value('city_id');

    if (! $zipId) {
        $this->markTestSkipped('No active zipcodes seeded.');
    }

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/shipping", [
            'streetAddress' => '123 Main St',
            'zipId'         => $zipId,
            'cityId'        => $cityId,
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    expect(
        DB::table('case_shipping_addresses')
            ->where('case_id', $caseId)
            ->where('zip_id', $zipId)
            ->exists()
    )->toBeTrue();
});

it('saves shipping with null zip_id when no zip is provided', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->postJson("/dev/cases/{$caseId}/shipping", [
            'streetAddress' => '123 Main St',
            'zipId'         => null,
        ])
        ->assertStatus(200)
        ->assertJson(['ok' => true]);

    expect(
        DB::table('case_shipping_addresses')
            ->where('case_id', $caseId)
            ->whereNull('zip_id')
            ->exists()
    )->toBeTrue();
});

it('rejects shipping save for a case belonging to another doctor', function () {
    ['caseId' => $caseId]    = makeDoctorCase();
    ['practiceId' => $otherPid] = makeDoctorCase();

    $this->withSession([ActivePractice::SESSION_KEY => $otherPid])
        ->postJson("/dev/cases/{$caseId}/shipping", [
            'streetAddress' => '123 Main St',
        ])
        ->assertStatus(404);
});
