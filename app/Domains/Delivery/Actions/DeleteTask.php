<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\Task;
use App\Domains\Delivery\Services\ProjectHealthCalculator;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteTask
{
    public function __construct(
        private AuditLogger $auditLogger,
        private ProjectHealthCalculator $healthCalculator,
    ) {}

    public function handle(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $projectId = $task->project_id;
            $snapshot = $task->toArray();
            $task->delete();
            $this->auditLogger->log('task.deleted', $task, $snapshot, null);
            ActivityHelper::log('Task deleted', $task, ['title' => $snapshot['title'] ?? null]);

            if ($projectId) {
                $this->healthCalculator->apply(Project::query()->findOrFail($projectId));
            }
        });
    }
}
