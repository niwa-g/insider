<?php

namespace Database\Factories\Delivery;

use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Enums\TaskPriority;
use App\Domains\Delivery\Enums\TaskStatus;
use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
            'due_date' => now()->addDays(fake()->numberBetween(3, 30))->toDateString(),
            'estimate_hours' => fake()->optional()->numberBetween(1, 16),
            'sort_order' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Task $task): void {
            if ($task->project_id) {
                $project = Project::query()->find($task->project_id);
                if ($project) {
                    $task->organization_id ??= $project->organization_id;
                    $task->client_id ??= $project->client_id;
                }
            }

            if (! $task->organization_id && $task->client_id) {
                $task->organization_id = Client::query()->find($task->client_id)?->organization_id;
            }
        });
    }
}
