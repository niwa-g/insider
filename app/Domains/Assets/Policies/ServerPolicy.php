<?php

namespace App\Domains\Assets\Policies;

use App\Domains\Assets\Models\Server;
use App\Models\User;

class ServerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('infrastructure.view');
    }

    public function view(User $user, Server $model): bool
    {
        return $user->can('infrastructure.view')
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('infrastructure.create');
    }

    public function update(User $user, Server $model): bool
    {
        return $user->can('infrastructure.update')
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, Server $model): bool
    {
        return $user->can('infrastructure.delete')
            && $user->organization_id === $model->organization_id;
    }
}
