<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;

// ─── Access control ─────────────────────────────────────────────
it('redirects guests from subcategories index', function () {
    $this->get('/admin/product-subcategories')->assertRedirect('/login');
});

// ─── Index ──────────────────────────────────────────────────────
it('lets an admin view subcategories index', function () {
    loginAsAdmin();
    ProductSubcategory::factory()->count(2)->create();

    $this->get('/admin/product-subcategories')
        ->assertOk()
        ->assertViewIs('admin.product-subcategories.index');
});

// ─── Store ──────────────────────────────────────────────────────
it('creates a subcategory with valid data and stores status as boolean true', function () {
    loginAsAdmin();
    $category = ProductCategory::factory()->create();

    $this->postJson('/admin/product-subcategories/ajax', [
        'category_id' => $category->id,
        'name'        => 'Premium Sub',
        'description' => 'Premium tier',
        'status'      => 'ACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    $sub = ProductSubcategory::where('name', 'Premium Sub')->first();
    expect($sub)->not->toBeNull();
    expect($sub->status)->toBeTrue(); // Controller converts ACTIVE -> true
});

it('stores status as boolean false when INACTIVE is submitted', function () {
    loginAsAdmin();
    $category = ProductCategory::factory()->create();

    $this->postJson('/admin/product-subcategories/ajax', [
        'category_id' => $category->id,
        'name'        => 'Inactive Sub',
        'status'      => 'INACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    expect(ProductSubcategory::where('name', 'Inactive Sub')->first()->status)->toBeFalse();
});

it('rejects subcategory creation when category_id does not exist', function () {
    loginAsAdmin();

    $this->postJson('/admin/product-subcategories/ajax', [
        'category_id' => 9999,
        'name'        => 'Bad Sub',
        'status'      => 'ACTIVE',
    ])->assertStatus(422)->assertJsonValidationErrors('category_id');
});

it('rejects subcategory creation when required fields are missing', function () {
    loginAsAdmin();

    $this->postJson('/admin/product-subcategories/ajax', [])
        ->assertStatus(422)->assertJsonValidationErrors(['category_id', 'name', 'status']);
});

// ─── Update ─────────────────────────────────────────────────────
it('updates a subcategory', function () {
    loginAsAdmin();
    $sub = ProductSubcategory::factory()->create(['name' => 'Old']);

    $this->putJson("/admin/product-subcategories/ajax/{$sub->id}", [
        'category_id' => $sub->category_id,
        'name'        => 'Renamed',
        'status'      => 'ACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    expect($sub->fresh()->name)->toBe('Renamed');
});

// ─── Destroy ────────────────────────────────────────────────────
it('soft-deletes a subcategory with no products', function () {
    loginAsAdmin();
    $sub = ProductSubcategory::factory()->create();

    $this->delete("/admin/product-subcategories/{$sub->id}")
        ->assertRedirect('/admin/product-subcategories');

    $this->assertSoftDeleted('products_subcategory', ['id' => $sub->id]);
});

it('blocks deletion of subcategory with products attached', function () {
    loginAsAdmin();
    $sub = ProductSubcategory::factory()->create();
    Product::factory()->create([
        'category_id'    => $sub->category_id,
        'subcategory_id' => $sub->id,
    ]);

    $this->delete("/admin/product-subcategories/{$sub->id}")
        ->assertSessionHas('error');

    $this->assertDatabaseHas('products_subcategory', [
        'id'         => $sub->id,
        'deleted_at' => null,
    ]);
});
