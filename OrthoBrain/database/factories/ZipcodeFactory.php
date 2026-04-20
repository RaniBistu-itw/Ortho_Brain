<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Zipcode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Zipcode>
 */
class ZipcodeFactory extends Factory
{
    protected $model = Zipcode::class;

    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'code'    => fake()->unique()->postcode(),
            'status'  => 'ACTIVE',
            'details' => fake()->sentence(),
        ];
    }
}
