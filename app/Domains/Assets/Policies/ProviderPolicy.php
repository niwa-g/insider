<?php

namespace App\Domains\Assets\Policies;

use App\Domains\Assets\Models\Provider;
use App\Models\User;

class ProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('providers.view');
    }

    public function view(User $user, Provider $model): bool
    {
        return $user->can('providers.view')
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('providers.create');
    }

    public function update(User $user, Provider $model): bool
    {
        return $user->can('providers.update')
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, Provider $model): bool
    {
        return $user->can('providers.delete')
            && $user->organization_id === $model->organization_id;
    }
}
