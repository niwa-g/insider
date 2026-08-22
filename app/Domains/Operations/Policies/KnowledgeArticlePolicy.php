<?php

namespace App\Domains\Operations\Policies;

use App\Domains\Operations\Models\KnowledgeArticle;
use App\Models\User;

class KnowledgeArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can("knowledge.view");
    }

    public function view(User $user, KnowledgeArticle $model): bool
    {
        return $user->can("knowledge.view")
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can("knowledge.create");
    }

    public function update(User $user, KnowledgeArticle $model): bool
    {
        return $user->can("knowledge.update")
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, KnowledgeArticle $model): bool
    {
        return $user->can("knowledge.delete")
            && $user->organization_id === $model->organization_id;
    }
}
