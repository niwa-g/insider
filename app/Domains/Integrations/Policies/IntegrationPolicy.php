<?php

namespace App\Domains\Integrations\Policies;

use App\Domains\Integrations\Models\Integration;
use App\Models\User;

class IntegrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('integrations.view');
    }

    public function view(User $user, Integration $model): bool
    {
        return $user->can('integrations.view')
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('integrations.create');
    }

    public function update(User $user, Integration $model): bool
    {
        return $user->can('integrations.update')
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, Integration $model): bool
    {
        return $user->can('integrations.delete')
            && $user->organization_id === $model->organization_id;
    }

    public function sync(User $user, Integration $model): bool
    {
        return $user->can('integrations.sync')
            && $user->organization_id === $model->organization_id;
    }
}
