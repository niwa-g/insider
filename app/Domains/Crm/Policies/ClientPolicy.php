<?php

namespace App\Domains\Crm\Policies;

use App\Domains\Crm\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clients.view');
    }

    public function view(User $user, Client $client): bool
    {
        return $user->can('clients.view')
            && $user->organization_id === $client->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('clients.create');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->can('clients.update')
            && $user->organization_id === $client->organization_id;
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->can('clients.delete')
            && $user->organization_id === $client->organization_id;
    }
}
