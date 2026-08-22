<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Delivery\Models\Project;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteProject
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Project $project): void
    {
        DB::transaction(function () use ($project) {
            $snapshot = $project->toArray();
            $project->delete();
            $this->auditLogger->log('project.deleted', $project, $snapshot, null);
            ActivityHelper::log('Project deleted', $project, ['name' => $snapshot['name'] ?? null]);
        });
    }
}
