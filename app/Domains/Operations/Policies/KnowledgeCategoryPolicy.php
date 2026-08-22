<?php

namespace App\Domains\Operations\Policies;

use App\Domains\Operations\Models\KnowledgeCategory;
use App\Models\User;

class KnowledgeCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can("knowledge.view");
    }

    public function view(User $user, KnowledgeCategory $model): bool
    {
        return $user->can("knowledge.view")
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can("knowledge.create");
    }

    public function update(User $user, KnowledgeCategory $model): bool
    {
        return $user->can("knowledge.update")
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, KnowledgeCategory $model): bool
    {
        return $user->can("knowledge.delete")
            && $user->organization_id === $model->organization_id;
    }
}
