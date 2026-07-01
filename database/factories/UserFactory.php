<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name'        => fake()->firstName(),
            'last_name'         => fake()->lastName(),
            'email'             => fake()->unique()->safeEmail(),
            'phone'             => '+237' . fake()->numerify('#########'),
            'email_verified_at' => now(),
            'password'          => 'Password1!',
            'status'            => 'active',
            'locale'            => 'fr',
            'timezone'          => 'Africa/Douala',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn() => ['email_verified_at' => null, 'status' => 'pending']);
    }

    public function suspended(): static
    {
        return $this->state(fn() => ['status' => 'suspended']);
    }
}
