<?php

use App\Models\ProductCategory;
use App\Models\ProductSubcategory;

// ─── Access control ─────────────────────────────────────────────
it('redirects guests from the categories index', function () {
    $this->get('/admin/product-categories')->assertRedirect('/login');
});

it('blocks non-admin (doctor) users from the categories index', function () {
    loginAsDoctor();
    $this->get('/admin/product-categories')->assertRedirect('/login');
});

// ─── Index ──────────────────────────────────────────────────────
it('lets an admin view the categories index', function () {
    loginAsAdmin();
    ProductCategory::factory()->count(3)->create();

    $this->get('/admin/product-categories')
        ->assertOk()
        ->assertViewIs('admin.product-categories.index');
});

it('filters categories by search term', function () {
    loginAsAdmin();
    ProductCategory::factory()->create(['name' => 'Aligner Kit']);
    ProductCategory::factory()->create(['name' => 'Retainer Pack']);

    $this->get('/admin/product-categories?search=Aligner')
        ->assertOk()
        ->assertSee('Aligner Kit')
        ->assertDontSee('Retainer Pack');
});

// ─── Store ──────────────────────────────────────────────────────
it('creates a category with valid data', function () {
    loginAsAdmin();

    $this->postJson('/admin/product-categories/ajax', [
        'name'   => 'New Category',
        'status' => 'ACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    $this->assertDatabaseHas('products_category', [
        'name'   => 'New Category',
        'status' => 'ACTIVE',
    ]);
});

it('rejects category creation when name is missing', function () {
    loginAsAdmin();

    $this->postJson('/admin/product-categories/ajax', ['status' => 'ACTIVE'])
        ->assertStatus(422)->assertJsonValidationErrors('name');
});

it('rejects category creation with invalid status', function () {
    loginAsAdmin();

    $this->postJson('/admin/product-categories/ajax', [
        'name'   => 'Test',
        'status' => 'PENDING',
    ])->assertStatus(422)->assertJsonValidationErrors('status');
});

// ─── Update ─────────────────────────────────────────────────────
it('updates an existing category', function () {
    loginAsAdmin();
    $category = ProductCategory::factory()->create(['name' => 'Old']);

    $this->putJson("/admin/product-categories/ajax/{$category->id}", [
        'name'   => 'Updated',
        'status' => 'INACTIVE',
    ])->assertOk()->assertJson(['ok' => true]);

    expect($category->fresh())
        ->name->toBe('Updated')
        ->status->toBe('INACTIVE');
});

// ─── Destroy ────────────────────────────────────────────────────
it('soft-deletes a category with no children', function () {
    loginAsAdmin();
    $category = ProductCategory::factory()->create();

    $this->delete("/admin/product-categories/{$category->id}")
        ->assertRedirect('/admin/product-categories');

    $this->assertSoftDeleted('products_category', ['id' => $category->id]);
});

it('blocks deletion of a category with subcategories attached', function () {
    loginAsAdmin();
    $category = ProductCategory::factory()->create();
    ProductSubcategory::factory()->create(['category_id' => $category->id]);

    $this->delete("/admin/product-categories/{$category->id}")
        ->assertSessionHas('error');

    $this->assertDatabaseHas('products_category', [
        'id'         => $category->id,
        'deleted_at' => null,
    ]);
});
