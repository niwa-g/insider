<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Delivery\Enums\TaskStatus;
use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\Task;
use App\Domains\Delivery\Services\ProjectHealthCalculator;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpsertTask
{
    public function __construct(
        private AuditLogger $auditLogger,
        private ProjectHealthCalculator $healthCalculator,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(?Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            if (isset($data['project_id']) && $data['project_id']) {
                $project = Project::query()->findOrFail($data['project_id']);
                $data['client_id'] = $project->client_id;
                $data['organization_id'] = $project->organization_id;
            }

            $status = $data['status'] ?? null;
            if ($status === TaskStatus::Done->value || $status === TaskStatus::Done) {
                $data['completed_at'] = $data['completed_at'] ?? now();
            } elseif ($status !== null) {
                $data['completed_at'] = null;
            }

            if ($task === null) {
                $data['reporter_id'] ??= Auth::id();
                $task = Task::query()->create($data);
                $this->auditLogger->log('task.created', $task, null, $task->toArray());
                ActivityHelper::log('Task created', $task, ['title' => $task->title]);
            } else {
                $old = $task->toArray();
                $task->update($data);
                $this->auditLogger->log('task.updated', $task, $old, $task->fresh()->toArray());
                ActivityHelper::log('Task updated', $task);
            }

            $fresh = $task->fresh(['project', 'assignee', 'client']);

            if ($fresh?->project_id) {
                $this->healthCalculator->apply(Project::query()->findOrFail($fresh->project_id));
            }

            return $fresh;
        });
    }
}
