<?php

namespace App\Domains\Assets\Policies;

use App\Domains\Assets\Models\Environment;
use App\Models\User;

class EnvironmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('environments.view');
    }

    public function view(User $user, Environment $model): bool
    {
        return $user->can('environments.view')
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('environments.create');
    }

    public function update(User $user, Environment $model): bool
    {
        return $user->can('environments.update')
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, Environment $model): bool
    {
        return $user->can('environments.delete')
            && $user->organization_id === $model->organization_id;
    }
}
