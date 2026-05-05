<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<State>
 */
class StateFactory extends Factory
{
    protected $model = State::class;

    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'name'       => fake()->unique()->state(),
            'state_code' => strtoupper(fake()->unique()->lexify('???')),
            'status'     => 'ACTIVE',
        ];
    }
}
