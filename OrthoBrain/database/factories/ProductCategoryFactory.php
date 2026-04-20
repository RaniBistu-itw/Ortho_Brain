<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        return [
            'name'   => fake()->unique()->words(2, true),
            'status' => 'ACTIVE',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'INACTIVE']);
    }
}
