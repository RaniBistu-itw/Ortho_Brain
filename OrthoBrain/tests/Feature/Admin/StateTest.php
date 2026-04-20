<?php

use App\Models\City;
use App\Models\Country;
use App\Models\State;

// ─── Access control ─────────────────────────────────────────────
it('redirects guests from states index', function () {
    $this->get('/admin/states')->assertRedirect('/login');
});

// ─── Index ──────────────────────────────────────────────────────
it('lets an admin view states index', function () {
    loginAsAdmin();
    State::factory()->count(2)->create();

    $this->get('/admin/states')
        ->assertOk()
        ->assertViewIs('admin.states.index');
});

// ─── Store ──────────────────────────────────────────────────────
it('creates a state with valid data', function () {
    loginAsAdmin();
    $country = Country::factory()->create();

    $this->post('/admin/states', [
        'country_id' => $country->id,
        'name'       => 'Bavaria',
        'state_code' => 'BY',
        'status'     => 'ACTIVE',
    ])->assertRedirect('/admin/states');

    $this->assertDatabaseHas('states', [
        'name'       => 'Bavaria',
        'state_code' => 'BY',
        'country_id' => $country->id,
    ]);
});

it('rejects state creation when country_id does not exist', function () {
    loginAsAdmin();

    $this->post('/admin/states', [
        'country_id' => 99999,
        'name'       => 'Nowhere',
        'state_code' => 'NW',
        'status'     => 'ACTIVE',
    ])->assertSessionHasErrors('country_id');
});

it('rejects duplicate state_code within the same country', function () {
    loginAsAdmin();
    $country = Country::factory()->create();
    State::factory()->create(['country_id' => $country->id, 'state_code' => 'DUP']);

    $this->post('/admin/states', [
        'country_id' => $country->id,
        'name'       => 'Second',
        'state_code' => 'DUP',
        'status'     => 'ACTIVE',
    ])->assertSessionHasErrors('state_code');
});

it('allows the same state_code in a different country', function () {
    loginAsAdmin();
    $countryA = Country::factory()->create();
    $countryB = Country::factory()->create();
    State::factory()->create(['country_id' => $countryA->id, 'state_code' => 'SHARED']);

    $this->post('/admin/states', [
        'country_id' => $countryB->id,
        'name'       => 'Shared Code State',
        'state_code' => 'SHARED',
        'status'     => 'ACTIVE',
    ])->assertRedirect('/admin/states');

    $this->assertDatabaseHas('states', [
        'country_id' => $countryB->id,
        'state_code' => 'SHARED',
    ]);
});

it('rejects state creation when required fields are missing', function () {
    loginAsAdmin();

    $this->post('/admin/states', [])
        ->assertSessionHasErrors(['country_id', 'name', 'state_code', 'status']);
});

// ─── Update ─────────────────────────────────────────────────────
it('updates a state', function () {
    loginAsAdmin();
    $state = State::factory()->create(['name' => 'Old']);

    $this->put("/admin/states/{$state->id}", [
        'country_id' => $state->country_id,
        'name'       => 'Renamed',
        'state_code' => $state->state_code,
        'status'     => 'ACTIVE',
    ])->assertRedirect('/admin/states');

    expect($state->fresh()->name)->toBe('Renamed');
});

// ─── Destroy ────────────────────────────────────────────────────
it('soft-deletes a state with no cities', function () {
    loginAsAdmin();
    $state = State::factory()->create();

    $this->delete("/admin/states/{$state->id}")
        ->assertRedirect('/admin/states');

    $this->assertSoftDeleted('states', ['id' => $state->id]);
});

it('blocks deletion of a state with cities attached', function () {
    loginAsAdmin();
    $state = State::factory()->create();
    City::factory()->create(['state_id' => $state->id]);

    $this->delete("/admin/states/{$state->id}")
        ->assertSessionHas('error');

    $this->assertDatabaseHas('states', [
        'id'         => $state->id,
        'deleted_at' => null,
    ]);
});
