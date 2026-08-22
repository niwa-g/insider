<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Delivery\Models\ProjectTemplate;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteProjectTemplate
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(ProjectTemplate $template): void
    {
        DB::transaction(function () use ($template) {
            $snapshot = $template->toArray();
            $template->items()->delete();
            $template->delete();
            $this->auditLogger->log('project_template.deleted', $template, $snapshot, null);
            ActivityHelper::log('Project template deleted', $template, ['name' => $snapshot['name'] ?? null]);
        });
    }
}
