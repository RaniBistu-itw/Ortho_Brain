<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'email'         => fake()->unique()->safeEmail(),
            'password_hash' => 'password',
            'role'          => 'DOCTOR',
            'is_active'     => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'ADMIN', 'is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
