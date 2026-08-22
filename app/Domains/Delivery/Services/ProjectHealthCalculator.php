<?php

namespace App\Domains\Delivery\Services;

use App\Domains\Delivery\Enums\ProjectHealthStatus;
use App\Domains\Delivery\Enums\ProjectStatus;
use App\Domains\Delivery\Enums\TaskStatus;
use App\Domains\Delivery\Models\Project;

class ProjectHealthCalculator
{
    /**
     * @return array{score: int, status: ProjectHealthStatus}
     */
    public function calculate(Project $project): array
    {
        $score = 100;

        $openTasks = $project->tasks()
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])
            ->get(['id', 'due_date', 'status']);

        $overdueCount = $openTasks
            ->filter(fn ($task) => $task->due_date && $task->due_date->isPast())
            ->count();

        $blockedCount = $openTasks
            ->filter(fn ($task) => $task->status === TaskStatus::Blocked)
            ->count();

        $score -= min(45, $overdueCount * 15);
        $score -= min(20, $blockedCount * 10);

        if (
            $project->due_date
            && $project->due_date->isPast()
            && ! in_array($project->status, [ProjectStatus::Completed, ProjectStatus::Cancelled], true)
        ) {
            $score -= 25;
        }

        if ($project->status === ProjectStatus::OnHold) {
            $score -= 10;
        }

        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'status' => ProjectHealthStatus::fromScore($score),
        ];
    }

    public function apply(Project $project): Project
    {
        $result = $this->calculate($project);

        $project->forceFill([
            'health_score' => $result['score'],
            'health_status' => $result['status'],
        ])->save();

        return $project->fresh();
    }
}
