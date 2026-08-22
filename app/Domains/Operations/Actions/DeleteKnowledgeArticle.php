<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\KnowledgeArticle;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteKnowledgeArticle
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(KnowledgeArticle $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('knowledge_article.deleted', $model, $snapshot, null);
            ActivityHelper::log('Knowledge article deleted', $model);
        });
    }
}
