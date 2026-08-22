<?php

namespace Database\Factories\Operations;

use App\Domains\Operations\Models\OpsNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OpsNotification> */
class OpsNotificationFactory extends Factory
{
    protected $model = OpsNotification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'ticket.assigned',
            'title' => fake()->sentence(4),
            'body' => fake()->optional()->sentence(),
            'data' => ['ticket_id' => fake()->uuid()],
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (OpsNotification $notification): void {
            if (! $notification->organization_id && $notification->user_id) {
                $notification->organization_id = User::query()->find($notification->user_id)?->organization_id;
            }
        });
    }
}
