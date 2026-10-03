<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(TicketType::cases()),
            'status' => TicketStatus::Aberta,
            'user_id' => User::factory()->state(['role' => UserRole::User]),
            'assigned_to' => User::factory()->state(['role' => UserRole::Ti]),
        ];
    }
}
