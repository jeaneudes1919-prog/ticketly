<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $priority = fake()->randomElement(TicketPriority::cases());

        return [
            'reference' => null,
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'status' => TicketStatus::Open->value,
            'priority' => $priority->value,
            'category_id' => null,
            'department_id' => null,
            'customer_id' => User::factory(),
            'assignee_id' => null,
            'due_at' => now()->addMinutes(480),
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Resolved->value,
            'resolved_at' => now(),
            'sla_breached' => false,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Closed->value,
            'resolved_at' => now()->subDay(),
            'closed_at' => now(),
        ]);
    }

    public function assignedTo(User $agent): static
    {
        return $this->state(fn () => ['assignee_id' => $agent->id]);
    }
}
