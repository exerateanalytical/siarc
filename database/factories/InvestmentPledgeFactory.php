<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Investors\Models\InvestmentPledge;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestmentPledgeFactory extends Factory
{
    protected $model = InvestmentPledge::class;

    public function definition(): array
    {
        return [
            'investor_id'      => User::factory(),
            'offering_id'      => ShareOffering::factory()->open(),
            'amount'           => $this->faker->numberBetween(10000, 5000000),
            'shares_requested' => $this->faker->numberBetween(1, 1000),
            'status'           => InvestmentPledge::STATUS_PENDING,
        ];
    }
}
