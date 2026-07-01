<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Payments\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition(): array
    {
        return [
            'user_id'            => User::factory(),
            'provider'           => $this->faker->randomElement(['mtn_momo', 'orange_money']),
            'type'               => 'payment',
            'status'             => 'pending',
            'amount'             => $this->faker->numberBetween(1000, 500000),
            'currency'           => 'XAF',
            'provider_reference' => null,
            'platform_reference' => 'PAY-' . strtoupper(Str::random(12)),
            'phone_number'       => '+2376' . $this->faker->numerify('########'),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function mtn(): static
    {
        return $this->state(fn () => ['provider' => 'mtn_momo']);
    }

    public function orange(): static
    {
        return $this->state(fn () => ['provider' => 'orange_money']);
    }
}
