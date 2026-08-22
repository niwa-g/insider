<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Delivery\Enums\ProjectStatus;
use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Services\ProjectHealthCalculator;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertProject
{
    public function __construct(
        private AuditLogger $auditLogger,
        private ProjectHealthCalculator $healthCalculator,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(?Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            if (($data['status'] ?? null) === ProjectStatus::Completed->value || ($data['status'] ?? null) === ProjectStatus::Completed) {
                $data['completed_at'] = $data['completed_at'] ?? now();
            }

            if ($project === null) {
                $project = Project::query()->create($data);
                $this->auditLogger->log('project.created', $project, null, $project->toArray());
                ActivityHelper::log('Project created', $project, ['name' => $project->name]);
            } else {
                $old = $project->toArray();
                $project->update($data);
                $this->auditLogger->log('project.updated', $project, $old, $project->fresh()->toArray());
                ActivityHelper::log('Project updated', $project);
            }

            return $this->healthCalculator->apply($project->fresh());
        });
    }
}
