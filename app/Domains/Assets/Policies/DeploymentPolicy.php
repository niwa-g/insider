<?php

namespace App\Domains\Assets\Policies;

use App\Domains\Assets\Models\Deployment;
use App\Models\User;

class DeploymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('deployments.view');
    }

    public function view(User $user, Deployment $model): bool
    {
        return $user->can('deployments.view')
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('deployments.create');
    }

    public function update(User $user, Deployment $model): bool
    {
        return $user->can('deployments.update')
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, Deployment $model): bool
    {
        return $user->can('deployments.delete')
            && $user->organization_id === $model->organization_id;
    }
}
