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
            'name'                => fake()->name(),
            'email'               => fake()->unique()->safeEmail(),
            'phone'               => '+237' . fake()->unique()->numerify('#########'),
            'password'            => 'Password1!',
            'status'              => 'active',
            'language_preference' => 'fr',
            'is_email_verified'   => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['is_email_verified' => false]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => 'suspended']);
    }
}
