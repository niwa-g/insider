<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Enums\KnowledgeArticleStatus;
use App\Domains\Operations\Models\KnowledgeArticle;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertKnowledgeArticle
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data */
    public function handle(?KnowledgeArticle $model, array $data): KnowledgeArticle
    {
        return DB::transaction(function () use ($model, $data) {
            if (($data['status'] ?? null) === KnowledgeArticleStatus::Published->value && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            if ($model === null) {
                $model = KnowledgeArticle::query()->create($data);
                $this->auditLogger->log('knowledge_article.created', $model, null, $model->toArray());
                ActivityHelper::log('Knowledge article created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('knowledge_article.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Knowledge article updated', $model);
            }

            return $model->fresh(['category', 'author']);
        });
    }
}
