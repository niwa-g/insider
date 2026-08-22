<?php

namespace Database\Factories\Operations;

use App\Domains\Crm\Models\Client;
use App\Domains\Operations\Enums\TicketPriority;
use App\Domains\Operations\Enums\TicketStatus;
use App\Domains\Operations\Enums\TicketType;
use App\Domains\Operations\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ticket> */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'number' => 'TCK-'.fake()->unique()->numerify('####'),
            'subject' => fake()->sentence(6),
            'description' => fake()->optional()->paragraph(),
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::Medium,
            'type' => TicketType::Incident,
            'sla_breached' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Ticket $ticket): void {
            if (! $ticket->organization_id && $ticket->client_id) {
                $ticket->organization_id = Client::query()->find($ticket->client_id)?->organization_id;
            }
        });
    }
}
