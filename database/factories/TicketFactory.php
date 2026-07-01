<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Support\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'category_id'   => null,
            'ticket_number' => 'TKT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'subject'       => $this->faker->sentence(5),
            'status'        => 'open',
            'priority'      => 'normal',
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed', 'resolved_at' => now()]);
    }
}
