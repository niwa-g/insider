<?php

namespace App\Domains\Assets\Policies;

use App\Domains\Assets\Models\CloudResource;
use App\Models\User;

class CloudResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('infrastructure.view');
    }

    public function view(User $user, CloudResource $model): bool
    {
        return $user->can('infrastructure.view')
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('infrastructure.create');
    }

    public function update(User $user, CloudResource $model): bool
    {
        return $user->can('infrastructure.update')
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, CloudResource $model): bool
    {
        return $user->can('infrastructure.delete')
            && $user->organization_id === $model->organization_id;
    }
}
