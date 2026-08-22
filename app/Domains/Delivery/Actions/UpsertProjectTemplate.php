<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Delivery\Models\ProjectTemplate;
use App\Domains\Delivery\Models\ProjectTemplateItem;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertProjectTemplate
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $items
     */
    public function handle(?ProjectTemplate $template, array $data, array $items = []): ProjectTemplate
    {
        return DB::transaction(function () use ($template, $data, $items) {
            if ($template === null) {
                $template = ProjectTemplate::query()->create($data);
                $this->auditLogger->log('project_template.created', $template, null, $template->toArray());
                ActivityHelper::log('Project template created', $template, ['name' => $template->name]);
            } else {
                $old = $template->toArray();
                $template->update($data);
                $this->auditLogger->log('project_template.updated', $template, $old, $template->fresh()->toArray());
                ActivityHelper::log('Project template updated', $template);
            }

            if ($items !== []) {
                $template->items()->delete();
                foreach (array_values($items) as $index => $item) {
                    ProjectTemplateItem::query()->create([
                        'project_template_id' => $template->id,
                        'title' => $item['title'],
                        'description' => $item['description'] ?? null,
                        'status' => $item['status'] ?? 'todo',
                        'priority' => $item['priority'] ?? 'medium',
                        'estimate_hours' => $item['estimate_hours'] ?? null,
                        'sort_order' => $item['sort_order'] ?? $index,
                    ]);
                }
            }

            return $template->fresh(['items']);
        });
    }
}
