<?php

namespace Database\Factories;

use App\Modules\Directory\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name'                => $name,
            'slug'                => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 99999),
            'trade_name'          => $this->faker->companySuffix(),
            'description_fr'      => $this->faker->sentence(),
            'description_en'      => $this->faker->sentence(),
            'legal_form'          => 'sarl',
            'status'              => 'active',
            'verification_status' => 'unverified',
            'email'               => $this->faker->companyEmail(),
            'phone'               => '+2376' . $this->faker->numerify('########'),
            'region_id'           => null,
            'city_id'             => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['verification_status' => 'verified']);
    }
}
