<?php

use App\Models\City;
use App\Models\State;
use App\Models\Zipcode;

// ─── Access control ─────────────────────────────────────────────
it('redirects guests from cities index', function () {
    $this->get('/admin/cities')->assertRedirect('/login');
});

// ─── Index ──────────────────────────────────────────────────────
it('lets an admin view cities index', function () {
    loginAsAdmin();
    City::factory()->count(2)->create();

    $this->get('/admin/cities')
        ->assertOk()
        ->assertViewIs('admin.cities.index');
});

// ─── Store ──────────────────────────────────────────────────────
it('creates a city with valid data', function () {
    loginAsAdmin();
    $state = State::factory()->create();

    $this->post('/admin/cities', [
        'state_id' => $state->id,
        'name'     => 'Mumbai',
        'status'   => 'ACTIVE',
    ])->assertRedirect('/admin/cities');

    $this->assertDatabaseHas('cities', [
        'name'     => 'Mumbai',
        'state_id' => $state->id,
    ]);
});

it('rejects city creation when state_id does not exist', function () {
    loginAsAdmin();

    $this->post('/admin/cities', [
        'state_id' => 99999,
        'name'     => 'Nowhere',
        'status'   => 'ACTIVE',
    ])->assertSessionHasErrors('state_id');
});

it('rejects city creation when required fields are missing', function () {
    loginAsAdmin();

    $this->post('/admin/cities', [])
        ->assertSessionHasErrors(['state_id', 'name', 'status']);
});

// ─── Update ─────────────────────────────────────────────────────
it('updates a city', function () {
    loginAsAdmin();
    $city = City::factory()->create(['name' => 'Old']);

    $this->put("/admin/cities/{$city->id}", [
        'state_id' => $city->state_id,
        'name'     => 'NewName',
        'status'   => 'ACTIVE',
    ])->assertRedirect('/admin/cities');

    expect($city->fresh()->name)->toBe('NewName');
});

// ─── Destroy ────────────────────────────────────────────────────
it('soft-deletes a city with no zipcodes', function () {
    loginAsAdmin();
    $city = City::factory()->create();

    $this->delete("/admin/cities/{$city->id}")
        ->assertRedirect('/admin/cities');

    $this->assertSoftDeleted('cities', ['id' => $city->id]);
});

it('blocks deletion of a city with zipcodes attached', function () {
    loginAsAdmin();
    $city = City::factory()->create();
    Zipcode::factory()->create(['city_id' => $city->id]);

    $this->delete("/admin/cities/{$city->id}")
        ->assertSessionHas('error');

    $this->assertDatabaseHas('cities', [
        'id'         => $city->id,
        'deleted_at' => null,
    ]);
});
