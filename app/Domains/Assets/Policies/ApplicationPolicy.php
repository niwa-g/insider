<?php

namespace App\Domains\Assets\Policies;

use App\Domains\Assets\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('applications.view');
    }

    public function view(User $user, Application $model): bool
    {
        return $user->can('applications.view')
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('applications.create');
    }

    public function update(User $user, Application $model): bool
    {
        return $user->can('applications.update')
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, Application $model): bool
    {
        return $user->can('applications.delete')
            && $user->organization_id === $model->organization_id;
    }
}
