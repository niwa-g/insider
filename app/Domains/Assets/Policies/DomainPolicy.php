<?php

namespace App\Domains\Assets\Policies;

use App\Domains\Assets\Models\Domain;
use App\Models\User;

class DomainPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('domains.view');
    }

    public function view(User $user, Domain $model): bool
    {
        return $user->can('domains.view')
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('domains.create');
    }

    public function update(User $user, Domain $model): bool
    {
        return $user->can('domains.update')
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, Domain $model): bool
    {
        return $user->can('domains.delete')
            && $user->organization_id === $model->organization_id;
    }
}
