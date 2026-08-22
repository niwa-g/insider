<?php

namespace App\Domains\Operations\Policies;

use App\Domains\Operations\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can("documents.view");
    }

    public function view(User $user, Document $model): bool
    {
        return $user->can("documents.view")
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can("documents.create");
    }

    public function update(User $user, Document $model): bool
    {
        return $user->can("documents.update")
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, Document $model): bool
    {
        return $user->can("documents.delete")
            && $user->organization_id === $model->organization_id;
    }
}
