<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\KnowledgeCategory;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteKnowledgeCategory
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(KnowledgeCategory $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('knowledge_category.deleted', $model, $snapshot, null);
            ActivityHelper::log('Knowledge category deleted', $model);
        });
    }
}
