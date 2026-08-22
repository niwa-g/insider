<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\ClientService;
use App\Models\User;

class ClientServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('client_services.view');
    }

    public function view(User $user, ClientService $service): bool
    {
        return $user->can('client_services.view')
            && $user->organization_id === $service->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('client_services.create');
    }

    public function update(User $user, ClientService $service): bool
    {
        return $user->can('client_services.update')
            && $user->organization_id === $service->organization_id;
    }

    public function delete(User $user, ClientService $service): bool
    {
        return $user->can('client_services.delete')
            && $user->organization_id === $service->organization_id;
    }
}
