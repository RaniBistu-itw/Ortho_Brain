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

    $this->post('/admin/scanners', [
        'name'            => 'TestScanner',
        'portal_link'     => 'https://portal.example.com',
        'portal_password' => 'secret',
        'description'     => 'desc',
        'status'          => 'ACTIVE',
    ])->assertRedirect('/admin/scanners');

    $this->assertDatabaseHas('scanners', [
        'name'   => 'TestScanner',
        'status' => 'ACTIVE',
    ]);
});

it('rejects scanner creation with missing required fields', function () {
    loginAsAdmin();

    $this->post('/admin/scanners', [])
        ->assertSessionHasErrors(['name', 'status']);
});

it('rejects scanner creation with invalid portal_link (not a URL)', function () {
    loginAsAdmin();

    $this->post('/admin/scanners', [
        'name'        => 'Scan',
        'portal_link' => 'not-a-url',
        'status'      => 'ACTIVE',
    ])->assertSessionHasErrors('portal_link');
});

// ─── Update ─────────────────────────────────────────────────────
it('updates a scanner', function () {
    loginAsAdmin();
    $scanner = Scanner::factory()->create(['name' => 'Old']);

    $this->put("/admin/scanners/{$scanner->id}", [
        'name'   => 'Updated',
        'status' => 'INACTIVE',
    ])->assertRedirect('/admin/scanners');

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
