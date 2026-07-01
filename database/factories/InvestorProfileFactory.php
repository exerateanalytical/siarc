<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Investors\Models\InvestorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestorProfileFactory extends Factory
{
    protected $model = InvestorProfile::class;

    public function definition(): array
    {
        return [
            'user_id'             => User::factory(),
            'investor_type'       => 'individual',
            'accreditation_level' => 'retail',
            'national_id'         => $this->faker->numerify('##########'),
            'id_type'             => 'CNI',
            'dob'                 => $this->faker->date('Y-m-d', '2000-01-01'),
            'nationality'         => 'CM',
            'occupation'          => $this->faker->jobTitle(),
            'annual_income'       => $this->faker->numberBetween(1000000, 50000000),
            'net_worth'           => $this->faker->numberBetween(2000000, 200000000),
            'risk_tolerance'      => 'moderate',
            'is_pep'              => false,
            'is_sanctioned'       => false,
        ];
    }
}
