<?php

namespace App\Domains\Crm\Policies;

use App\Domains\Crm\Models\ClientGroup;
use App\Models\User;

class ClientGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('client_groups.view');
    }

    public function view(User $user, ClientGroup $clientGroup): bool
    {
        return $user->can('client_groups.view')
            && $user->organization_id === $clientGroup->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('client_groups.create');
    }

    public function update(User $user, ClientGroup $clientGroup): bool
    {
        return $user->can('client_groups.update')
            && $user->organization_id === $clientGroup->organization_id;
    }

    public function delete(User $user, ClientGroup $clientGroup): bool
    {
        return $user->can('client_groups.delete')
            && $user->organization_id === $clientGroup->organization_id;
    }
}
