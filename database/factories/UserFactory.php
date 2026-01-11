<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->numerify('09#########'),

            // optional fields if your table has them
            'is_active' => $this->faker->boolean(70),
            'phone_verified_at' => now(),

            'password' => Hash::make('password'), // for local dev
            'remember_token' => Str::random(20),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function unverifiedPhone(): static
    {
        return $this->state(fn () => ['phone_verified_at' => null]);
    }
}
