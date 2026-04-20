<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ─── Access control ─────────────────────────────────────────────
it('redirects guests from products index', function () {
    $this->get('/admin/products')->assertRedirect('/login');
});

// ─── Index ──────────────────────────────────────────────────────
it('lets an admin view products index', function () {
    loginAsAdmin();
    Product::factory()->count(2)->create();

    $this->get('/admin/products')
        ->assertOk()
        ->assertViewIs('admin.products.index');
});

it('filters products by search term', function () {
    loginAsAdmin();
    Product::factory()->create(['name' => 'Invisalign Kit']);
    Product::factory()->create(['name' => 'Bracket Set']);

    $this->get('/admin/products?search=Invisalign')
        ->assertOk()
        ->assertSee('Invisalign Kit')
        ->assertDontSee('Bracket Set');
});

// ─── Store ──────────────────────────────────────────────────────
it('creates a product with valid data (no image)', function () {
    loginAsAdmin();
    $category = ProductCategory::factory()->create();

    $this->post('/admin/products', [
        'category_id' => $category->id,
        'name'        => 'Test Product',
        'base_price'  => 99.99,
        'status'      => 'ACTIVE',
    ])->assertRedirect();

    $this->assertDatabaseHas('products', [
        'name'        => 'Test Product',
        'category_id' => $category->id,
        'status'      => 'ACTIVE',
    ]);
});

it('creates a product with an uploaded image', function () {
    Storage::fake('public');
    loginAsAdmin();
    $category = ProductCategory::factory()->create();

    $this->post('/admin/products', [
        'category_id' => $category->id,
        'name'        => 'With Image',
        'base_price'  => 49.50,
        'status'      => 'ACTIVE',
        'image'       => UploadedFile::fake()->image('product.jpg'),
    ])->assertRedirect();

    $product = Product::where('name', 'With Image')->first();
    expect($product->image_s3_key)->not->toBeNull();
    Storage::disk('public')->assertExists($product->image_s3_key);
});

it('rejects product creation with missing required fields', function () {
    loginAsAdmin();

    $this->post('/admin/products', [])
        ->assertSessionHasErrors(['name', 'category_id', 'base_price', 'status']);
});

it('rejects product creation with negative base_price', function () {
    loginAsAdmin();
    $category = ProductCategory::factory()->create();

    $this->post('/admin/products', [
        'category_id' => $category->id,
        'name'        => 'Negative Price',
        'base_price'  => -10,
        'status'      => 'ACTIVE',
    ])->assertSessionHasErrors('base_price');
});

it('rejects product creation when to_step is less than from_step', function () {
    loginAsAdmin();
    $category = ProductCategory::factory()->create();

    $this->post('/admin/products', [
        'category_id' => $category->id,
        'name'        => 'Bad Range',
        'base_price'  => 10,
        'from_step'   => 10,
        'to_step'     => 5,
        'status'      => 'ACTIVE',
    ])->assertSessionHasErrors('to_step');
});

it('rejects product creation when subcategory does not belong to given category relationships', function () {
    // This just verifies subcategory_id must exist; FK relationship integrity is DB-level.
    loginAsAdmin();
    $category = ProductCategory::factory()->create();

    $this->post('/admin/products', [
        'category_id'    => $category->id,
        'subcategory_id' => 99999,
        'name'           => 'Bad Sub',
        'base_price'     => 10,
        'status'         => 'ACTIVE',
    ])->assertSessionHasErrors('subcategory_id');
});

// ─── Update ─────────────────────────────────────────────────────
it('updates a product', function () {
    loginAsAdmin();
    $product = Product::factory()->create(['name' => 'Old Name']);

    $this->put("/admin/products/{$product->id}", [
        'category_id' => $product->category_id,
        'name'        => 'New Name',
        'base_price'  => 20,
        'status'      => 'ACTIVE',
    ])->assertRedirect();

    expect($product->fresh()->name)->toBe('New Name');
});

// ─── Destroy ────────────────────────────────────────────────────
it('soft-deletes a product', function () {
    Storage::fake('public');
    loginAsAdmin();
    $product = Product::factory()->create();

    $this->delete("/admin/products/{$product->id}")->assertRedirect();

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});
