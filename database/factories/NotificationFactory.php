<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'type'       => 'system',
            'title_fr'   => $this->faker->sentence(4),
            'title_en'   => $this->faker->sentence(4),
            'body_fr'    => $this->faker->paragraph(),
            'body_en'    => $this->faker->paragraph(),
            'data'       => [],
            'action_url' => null,
            'read_at'    => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }
}
