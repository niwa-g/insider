<?php

namespace Database\Factories\Delivery;

use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Enums\ProjectHealthStatus;
use App\Domains\Delivery\Enums\ProjectPriority;
use App\Domains\Delivery\Enums\ProjectStatus;
use App\Domains\Delivery\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = fake()->unique()->catchPhrase();

        return [
            'client_id' => Client::factory(),
            'name' => $name,
            'code' => strtoupper(Str::slug(Str::limit($name, 20, ''), '_')),
            'description' => fake()->optional()->sentence(),
            'status' => ProjectStatus::Active,
            'priority' => ProjectPriority::Medium,
            'health_status' => ProjectHealthStatus::Healthy,
            'health_score' => 100,
            'start_date' => now()->subWeeks(2)->toDateString(),
            'due_date' => now()->addWeeks(6)->toDateString(),
            'budget' => fake()->optional()->randomFloat(2, 1000, 50000),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Project $project): void {
            if (! $project->organization_id && $project->client_id) {
                $project->organization_id = Client::query()->find($project->client_id)?->organization_id;
            }
        });
    }
}
