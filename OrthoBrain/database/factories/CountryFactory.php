<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'name'         => fake()->unique()->country(),
            'country_code' => strtoupper(fake()->unique()->lexify('??')),
            'phone_code'   => '+' . fake()->unique()->numberBetween(100, 9999),
            'status'       => 'ACTIVE',
        ];
    }
}
