<?php

namespace Database\Factories;

use App\Models\Scanner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Scanner>
 */
class ScannerFactory extends Factory
{
    protected $model = Scanner::class;

    public function definition(): array
    {
        return [
            'name'            => fake()->unique()->company(),
            'description'     => fake()->sentence(),
            'portal_password' => 'secret123',
            'portal_link'     => fake()->url(),
            'status'          => 'ACTIVE',
        ];
    }
}
