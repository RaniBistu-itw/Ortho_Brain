<?php

use App\Models\City;
use App\Models\Zipcode;

// ─── Access control ─────────────────────────────────────────────
it('redirects guests from zipcodes index', function () {
    $this->get('/admin/zipcodes')->assertRedirect('/login');
});

// ─── Index ──────────────────────────────────────────────────────
it('lets an admin view zipcodes index', function () {
    loginAsAdmin();
    Zipcode::factory()->count(2)->create();

    $this->get('/admin/zipcodes')
        ->assertOk()
        ->assertViewIs('admin.zipcodes.index');
});

// ─── Store ──────────────────────────────────────────────────────
it('creates a zipcode with valid data', function () {
    loginAsAdmin();
    $city = City::factory()->create();

    $this->postJson('/admin/zipcodes/ajax', [
        'city_id' => $city->id,
        'code'    => '400001',
        'details' => 'Downtown',
        'status'  => 'ACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    $this->assertDatabaseHas('zipcodes', [
        'code'    => '400001',
        'city_id' => $city->id,
    ]);
});

it('rejects zipcode creation when city_id does not exist', function () {
    loginAsAdmin();

    $this->postJson('/admin/zipcodes/ajax', [
        'city_id' => 99999,
        'code'    => '000000',
        'status'  => 'ACTIVE',
    ])->assertStatus(422)->assertJsonValidationErrors('city_id');
});

it('rejects zipcode creation when required fields are missing', function () {
    loginAsAdmin();

    $this->postJson('/admin/zipcodes/ajax', [])
        ->assertStatus(422)->assertJsonValidationErrors(['city_id', 'code', 'status']);
});

// ─── Update ─────────────────────────────────────────────────────
it('updates a zipcode', function () {
    loginAsAdmin();
    $zipcode = Zipcode::factory()->create(['code' => '111111']);

    $this->putJson("/admin/zipcodes/ajax/{$zipcode->id}", [
        'city_id' => $zipcode->city_id,
        'code'    => '222222',
        'status'  => 'ACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    expect($zipcode->fresh()->code)->toBe('222222');
});

// ─── Destroy ────────────────────────────────────────────────────
it('soft-deletes a zipcode', function () {
    loginAsAdmin();
    $zipcode = Zipcode::factory()->create();

    $this->delete("/admin/zipcodes/{$zipcode->id}")
        ->assertRedirect('/admin/zipcodes');

    $this->assertSoftDeleted('zipcodes', ['id' => $zipcode->id]);
});
