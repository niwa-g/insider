<?php

namespace Database\Factories\Assets;

use App\Domains\Assets\Enums\ApplicationStatus;
use App\Domains\Assets\Models\Application;
use App\Domains\Crm\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Application> */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'client_id' => Client::factory(),
            'name' => ucwords($name),
            'code' => strtoupper(Str::slug(Str::limit($name, 20, ''), '_')),
            'description' => fake()->optional()->sentence(),
            'stack' => fake()->randomElement(['Laravel + React', 'Node + Vue', 'WordPress']),
            'status' => ApplicationStatus::Active,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Application $application): void {
            if (! $application->organization_id && $application->client_id) {
                $application->organization_id = Client::query()->find($application->client_id)?->organization_id;
            }
        });
    }
}
