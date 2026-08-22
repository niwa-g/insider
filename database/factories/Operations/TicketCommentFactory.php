<?php

namespace Database\Factories\Operations;

use App\Domains\Operations\Models\Ticket;
use App\Domains\Operations\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TicketComment> */
class TicketCommentFactory extends Factory
{
    protected $model = TicketComment::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_internal' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (TicketComment $comment): void {
            if (! $comment->organization_id && $comment->ticket_id) {
                $comment->organization_id = Ticket::query()->find($comment->ticket_id)?->organization_id;
            }
        });
    }
}
