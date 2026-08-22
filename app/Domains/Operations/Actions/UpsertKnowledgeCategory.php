<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\KnowledgeCategory;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertKnowledgeCategory
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data */
    public function handle(?KnowledgeCategory $model, array $data): KnowledgeCategory
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = KnowledgeCategory::query()->create($data);
                $this->auditLogger->log('knowledge_category.created', $model, null, $model->toArray());
                ActivityHelper::log('Knowledge category created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('knowledge_category.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Knowledge category updated', $model);
            }

            return $model->fresh();
        });
    }
}
