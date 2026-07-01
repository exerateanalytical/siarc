<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Trading\Models\Order;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 100);
        $price    = $this->faker->numberBetween(1000, 50000);

        return [
            'offering_id'  => ShareOffering::factory(),
            'investor_id'  => User::factory(),
            'type'         => 'buy',
            'status'       => 'pending',
            'quantity'     => $quantity,
            'unit_price'   => $price,
            'total_amount' => $quantity * $price,
        ];
    }
}
