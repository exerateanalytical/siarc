<?php

namespace Database\Factories;

use App\Modules\Directory\Models\Company;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShareOfferingFactory extends Factory
{
    protected $model = ShareOffering::class;

    public function definition(): array
    {
        $sharePrice  = $this->faker->numberBetween(1000, 50000);
        $totalShares = $this->faker->numberBetween(1000, 100000);

        return [
            'company_id'       => Company::factory(),
            'title_fr'         => 'Offre ' . $this->faker->unique()->company(),
            'title_en'         => 'Offering ' . $this->faker->company(),
            'summary_fr'       => $this->faker->paragraph(),
            'summary_en'       => $this->faker->paragraph(),
            'instrument_type'  => 'ordinary_shares',
            'status'           => ShareOffering::STATUS_DRAFT,
            'target_amount'    => $sharePrice * $totalShares,
            'minimum_amount'   => $sharePrice * (int) ($totalShares / 2),
            'maximum_amount'   => null,
            'amount_raised'    => 0,
            'share_price'      => $sharePrice,
            'total_shares'     => $totalShares,
            'shares_sold'      => 0,
            'equity_offered'   => $this->faker->randomFloat(2, 1, 40),
            'min_investment'   => 10000,
            'max_investment'   => null,
            'open_date'        => now()->toDateString(),
            'close_date'       => now()->addMonths(3)->toDateString(),
            'currency'         => 'XAF',
            'platform_fee_pct' => 2.50,
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => ['status' => ShareOffering::STATUS_OPEN]);
    }

    public function pendingCmf(): static
    {
        return $this->state(fn () => ['status' => ShareOffering::STATUS_PENDING_CMF]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status'          => ShareOffering::STATUS_CMF_APPROVED,
            'cmf_approved_at' => now(),
        ]);
    }
}
