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
            // Every real account is an artisan and the signup handler defaults
            // to one, so a user with no account type was never a realistic
            // fixture. It started mattering when the shop form began refusing
            // non-sellers: a typeless user is not a seller, and six tests that
            // meant "a seller" were silently relying on the omission.
            'account_type'        => 'artisan',
            'language_preference' => 'fr',
            'is_email_verified'   => true,
        ];
    }

    public function buyer(): static
    {
        return $this->state(fn () => ['account_type' => 'buyer']);
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
