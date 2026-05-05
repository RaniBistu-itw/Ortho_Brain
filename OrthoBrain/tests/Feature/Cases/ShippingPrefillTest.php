<?php

use App\Models\User;
use App\Support\ActivePractice;
use Illuminate\Support\Facades\DB;

// ─── serializeShipping → text-field prefill ───────────────────────────────────
//
// Locks down the contract that the case-edit page exposes BOTH the FK ids
// (zipId, cityId, stateId, countryId) AND the resolved text (zipCode, city,
// state, country). The frontend's _hydrateFromPrefill reads the text fields
// directly — without them, edit-mode reload shows blank city/state/country.

/**
 * Insert a minimal geo chain (country → state → city → zipcode) so the test is
 * self-contained and does not depend on whether the test DB was seeded with
 * the geo masters.
 */
function makeGeoChain(): array
{
    $now = now();

    $countryId = DB::table('countries')->insertGetId([
        'name' => 'Testland', 'country_code' => 'TL', 'phone_code' => '+99',
        'status' => 'ACTIVE', 'created_at' => $now, 'updated_at' => $now,
    ]);
    $stateId = DB::table('states')->insertGetId([
        'country_id' => $countryId, 'name' => 'Test State', 'state_code' => 'TST',
        'status' => 'ACTIVE', 'created_at' => $now, 'updated_at' => $now,
    ]);
    $cityId = DB::table('cities')->insertGetId([
        'state_id' => $stateId, 'name' => 'Test City',
        'status' => 'ACTIVE', 'created_at' => $now, 'updated_at' => $now,
    ]);
    $zipId = DB::table('zipcodes')->insertGetId([
        'code' => '12345', 'city_id' => $cityId,
        'status' => 'ACTIVE', 'created_at' => $now, 'updated_at' => $now,
    ]);

    return [
        'zipId'       => $zipId,       'zipCode'     => '12345',
        'cityId'      => $cityId,      'cityName'    => 'Test City',
        'stateId'     => $stateId,     'stateName'   => 'Test State',
        // The shipping prefill exposes the country ISO code (matches the
        // <select> options + zip-cascade payload), not the display name.
        'countryId'   => $countryId,   'countryCode' => 'TL',
    ];
}

it('exposes resolved shipping text fields in the doctor edit prefill', function () {
    $geo = makeGeoChain();
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    DB::table('case_shipping_addresses')->insert([
        'case_id'          => $caseId,
        'practice_name'    => 'Test Practice',
        'doctor_name'      => 'Dr Test',
        'street_address_1' => '123 Main St',
        'street_address_2' => 'Apt 4',
        'zip_id'           => $geo['zipId'],
        'city_id'          => $geo['cityId'],
        'state_id'         => $geo['stateId'],
        'country_id'       => $geo['countryId'],
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/edit")
        ->assertStatus(200);

    $prefill = extractShippingPrefill($response->getContent());

    expect($prefill)->toMatchArray([
        'practice'       => 'Test Practice',
        'doctorName'     => 'Dr Test',
        'streetAddress'  => '123 Main St',
        'streetAddress2' => 'Apt 4',
        'zipId'          => $geo['zipId'],
        'zipCode'        => $geo['zipCode'],
        'cityId'         => $geo['cityId'],
        'city'           => $geo['cityName'],
        'stateId'        => $geo['stateId'],
        'state'          => $geo['stateName'],
        'countryId'      => $geo['countryId'],
        'country'        => $geo['countryCode'],
    ]);
});

it('returns explicit nulls (not missing keys) for shipping with null FKs', function () {
    ['practiceId' => $pid, 'caseId' => $caseId] = makeDoctorCase();

    DB::table('case_shipping_addresses')->insert([
        'case_id'          => $caseId,
        'practice_name'    => null,
        'doctor_name'      => null,
        'street_address_1' => 'Free-form address',
        'street_address_2' => null,
        'zip_id'           => null,
        'city_id'          => null,
        'state_id'         => null,
        'country_id'       => null,
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);

    $response = $this->withSession([ActivePractice::SESSION_KEY => $pid])
        ->get("/dev/cases/{$caseId}/edit")
        ->assertStatus(200);

    $prefill = extractShippingPrefill($response->getContent());

    // All text-resolution keys must be present in the array, with explicit null
    // values. Missing keys would let `?->` refactors hide regressions.
    foreach (['zipCode', 'city', 'state', 'country', 'zipId', 'cityId', 'stateId', 'countryId'] as $key) {
        expect($prefill)->toHaveKey($key);
        expect($prefill[$key])->toBeNull();
    }
});

it('exposes the same shipping text fields on the admin edit prefill', function () {
    $geo = makeGeoChain();
    ['caseId' => $caseId] = makeDoctorCase();

    DB::table('case_shipping_addresses')->insert([
        'case_id'          => $caseId,
        'street_address_1' => '99 Admin Way',
        'zip_id'           => $geo['zipId'],
        'city_id'          => $geo['cityId'],
        'state_id'         => $geo['stateId'],
        'country_id'       => $geo['countryId'],
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);

    // Switch from the doctor created by makeDoctorCase() to an admin actor.
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $response = $this->get("/admin/cases/{$caseId}/edit")
        ->assertStatus(200);

    $prefill = extractShippingPrefill($response->getContent());

    expect($prefill['streetAddress'])->toBe('99 Admin Way');
    expect($prefill['zipCode'])->toBe($geo['zipCode']);
    expect($prefill['city'])->toBe($geo['cityName']);
    expect($prefill['state'])->toBe($geo['stateName']);
    expect($prefill['country'])->toBe($geo['countryCode']);
});

/**
 * Pull the JSON literal assigned to `window.__shippingAddressPrefill = ...;`
 * out of the rendered edit-page HTML, decode it, and return the array.
 */
function extractShippingPrefill(string $html): array
{
    preg_match('/window\.__shippingAddressPrefill\s*=\s*(\{[^;]*\});/', $html, $m);
    expect($m[1] ?? null)->not->toBeNull('shippingAddressPrefill JSON not found in response');
    return json_decode($m[1], true);
}
