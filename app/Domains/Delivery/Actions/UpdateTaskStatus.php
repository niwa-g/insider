<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Delivery\Enums\TaskStatus;
use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\Task;
use App\Domains\Delivery\Services\ProjectHealthCalculator;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpdateTaskStatus
{
    public function __construct(
        private AuditLogger $auditLogger,
        private ProjectHealthCalculator $healthCalculator,
    ) {}

    public function handle(Task $task, TaskStatus|string $status, ?int $sortOrder = null): Task
    {
        return DB::transaction(function () use ($task, $status, $sortOrder) {
            $old = $task->toArray();
            $statusEnum = $status instanceof TaskStatus ? $status : TaskStatus::from($status);

            $payload = [
                'status' => $statusEnum,
                'completed_at' => $statusEnum === TaskStatus::Done ? ($task->completed_at ?? now()) : null,
            ];

            if ($sortOrder !== null) {
                $payload['sort_order'] = $sortOrder;
            }

            $task->update($payload);
            $this->auditLogger->log('task.status_updated', $task, $old, $task->fresh()->toArray());
            ActivityHelper::log('Task status updated', $task, ['status' => $statusEnum->value]);

            if ($task->project_id) {
                $this->healthCalculator->apply(Project::query()->findOrFail($task->project_id));
            }

            return $task->fresh(['assignee', 'project', 'client']);
        });
    }
}
