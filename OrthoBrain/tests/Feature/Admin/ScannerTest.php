<?php

use App\Models\Scanner;

// ─── Access control ─────────────────────────────────────────────
it('redirects guests from scanners index', function () {
    $this->get('/admin/scanners')->assertRedirect('/login');
});

// ─── Index ──────────────────────────────────────────────────────
it('lets an admin view scanners index', function () {
    loginAsAdmin();
    Scanner::factory()->count(2)->create();

    $this->get('/admin/scanners')
        ->assertOk()
        ->assertViewIs('admin.scanners.index');
});

it('filters scanners by search', function () {
    loginAsAdmin();
    Scanner::factory()->create(['name' => 'iTero Scanner']);
    Scanner::factory()->create(['name' => '3Shape Scanner']);

    $this->get('/admin/scanners?search=iTero')
        ->assertOk()
        ->assertSee('iTero')
        ->assertDontSee('3Shape');
});

// ─── Store ──────────────────────────────────────────────────────
it('creates a scanner with valid data', function () {
    loginAsAdmin();

    $this->postJson('/admin/scanners/ajax', [
        'name'            => 'TestScanner',
        'portal_link'     => 'https://portal.example.com',
        'portal_password' => 'secret',
        'description'     => 'desc',
        'status'          => 'ACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    $this->assertDatabaseHas('scanners', [
        'name'   => 'TestScanner',
        'status' => 'ACTIVE',
    ]);
});

it('rejects scanner creation with missing required fields', function () {
    loginAsAdmin();

    $this->postJson('/admin/scanners/ajax', [])
        ->assertStatus(422)->assertJsonValidationErrors(['name', 'status']);
});

it('rejects scanner creation with invalid portal_link (not a URL)', function () {
    loginAsAdmin();

    $this->postJson('/admin/scanners/ajax', [
        'name'        => 'Scan',
        'portal_link' => 'not-a-url',
        'status'      => 'ACTIVE',
    ])->assertStatus(422)->assertJsonValidationErrors('portal_link');
});

// ─── Update ─────────────────────────────────────────────────────
it('updates a scanner', function () {
    loginAsAdmin();
    $scanner = Scanner::factory()->create(['name' => 'Old']);

    $this->putJson("/admin/scanners/ajax/{$scanner->id}", [
        'name'   => 'Updated',
        'status' => 'INACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    expect($scanner->fresh())
        ->name->toBe('Updated')
        ->status->toBe('INACTIVE');
});

// ─── Destroy ────────────────────────────────────────────────────
it('soft-deletes a scanner', function () {
    loginAsAdmin();
    $scanner = Scanner::factory()->create();

    $this->delete("/admin/scanners/{$scanner->id}")
        ->assertRedirect('/admin/scanners');

    $this->assertSoftDeleted('scanners', ['id' => $scanner->id]);
});
