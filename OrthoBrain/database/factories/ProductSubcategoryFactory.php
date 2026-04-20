<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductSubcategory>
 */
class ProductSubcategoryFactory extends Factory
{
    protected $model = ProductSubcategory::class;

    public function definition(): array
    {
        return [
            'category_id' => ProductCategory::factory(),
            'name'        => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'status'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => false]);
    }
}
