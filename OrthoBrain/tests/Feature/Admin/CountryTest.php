<?php

use App\Models\Country;
use App\Models\State;

// ─── Access control ─────────────────────────────────────────────
it('redirects guests from countries index', function () {
    $this->get('/admin/countries')->assertRedirect('/login');
});

// ─── Index ──────────────────────────────────────────────────────
it('lets an admin view countries index', function () {
    loginAsAdmin();
    Country::factory()->count(2)->create();

    $this->get('/admin/countries')
        ->assertOk()
        ->assertViewIs('admin.countries.index');
});

it('filters countries by search', function () {
    loginAsAdmin();
    Country::factory()->create(['name' => 'India', 'country_code' => 'IN']);
    Country::factory()->create(['name' => 'Germany', 'country_code' => 'DE']);

    $this->get('/admin/countries?search=India')
        ->assertOk()
        ->assertSee('India')
        ->assertDontSee('Germany');
});

// ─── Store ──────────────────────────────────────────────────────
it('creates a country with valid data', function () {
    loginAsAdmin();

    $this->postJson('/admin/countries/ajax', [
        'name'         => 'Testland',
        'country_code' => 'TL',
        'phone_code'   => '+999',
        'status'       => 'ACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    $this->assertDatabaseHas('countries', [
        'name'         => 'Testland',
        'country_code' => 'TL',
    ]);
});

it('rejects country creation with a duplicate country_code', function () {
    loginAsAdmin();
    Country::factory()->create(['country_code' => 'US']);

    $this->postJson('/admin/countries/ajax', [
        'name'         => 'Another',
        'country_code' => 'US',
        'phone_code'   => '+1',
        'status'       => 'ACTIVE',
    ])->assertStatus(422)->assertJsonValidationErrors('country_code');
});

it('rejects country creation when required fields are missing', function () {
    loginAsAdmin();

    $this->postJson('/admin/countries/ajax', [])
        ->assertStatus(422)->assertJsonValidationErrors(['name', 'country_code', 'phone_code', 'status']);
});

// ─── Update ─────────────────────────────────────────────────────
it('allows updating a country with its own country_code (unique ignore)', function () {
    loginAsAdmin();
    $country = Country::factory()->create(['country_code' => 'FR']);

    $this->putJson("/admin/countries/ajax/{$country->id}", [
        'name'         => 'France Updated',
        'country_code' => 'FR',
        'phone_code'   => '+33',
        'status'       => 'ACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    expect($country->fresh()->name)->toBe('France Updated');
});

// ─── Destroy ────────────────────────────────────────────────────
it('soft-deletes a country with no states', function () {
    loginAsAdmin();
    $country = Country::factory()->create();

    $this->delete("/admin/countries/{$country->id}")
        ->assertRedirect('/admin/countries');

    $this->assertSoftDeleted('countries', ['id' => $country->id]);
});

it('blocks deletion of a country with states attached', function () {
    loginAsAdmin();
    $country = Country::factory()->create();
    State::factory()->create(['country_id' => $country->id]);

    $this->delete("/admin/countries/{$country->id}")
        ->assertSessionHas('error');

    $this->assertDatabaseHas('countries', [
        'id'         => $country->id,
        'deleted_at' => null,
    ]);
});
