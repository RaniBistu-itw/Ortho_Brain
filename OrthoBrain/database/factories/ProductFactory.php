<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name'                => fake()->unique()->words(3, true),
            'category_id'         => ProductCategory::factory(),
            'subcategory_id'      => null,
            'base_price'          => fake()->randomFloat(2, 10, 500),
            'number_of_revisions' => fake()->numberBetween(0, 10),
            'product_term_months' => fake()->numberBetween(1, 24),
            'from_step'           => 0,
            'to_step'             => fake()->numberBetween(5, 20),
            'url'                 => fake()->url(),
            'status'              => 'ACTIVE',
            'image_s3_key'        => null,
            'description'         => fake()->sentence(),
        ];
    }
}
