<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\ProjectTemplate;
use App\Domains\Delivery\Models\Task;
use App\Domains\Delivery\Services\ProjectHealthCalculator;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplyProjectTemplate
{
    public function __construct(
        private AuditLogger $auditLogger,
        private ProjectHealthCalculator $healthCalculator,
    ) {}

    public function handle(Project $project, ProjectTemplate $template): Project
    {
        return DB::transaction(function () use ($project, $template) {
            $template->loadMissing('items');

            $project->update([
                'project_template_id' => $template->id,
            ]);

            foreach ($template->items as $item) {
                Task::query()->create([
                    'organization_id' => $project->organization_id,
                    'client_id' => $project->client_id,
                    'project_id' => $project->id,
                    'project_template_item_id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'status' => $item->status,
                    'priority' => $item->priority,
                    'estimate_hours' => $item->estimate_hours,
                    'sort_order' => $item->sort_order,
                    'reporter_id' => Auth::id(),
                ]);
            }

            $this->auditLogger->log('project.template_applied', $project, null, [
                'template_id' => $template->id,
                'items' => $template->items->count(),
            ]);
            ActivityHelper::log('Project template applied', $project, ['template' => $template->name]);

            return $this->healthCalculator->apply($project->fresh());
        });
    }
}
