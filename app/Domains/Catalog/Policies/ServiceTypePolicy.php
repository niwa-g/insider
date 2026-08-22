<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\ServiceType;
use App\Models\User;

class ServiceTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('service_catalog.view');
    }

    public function view(User $user, ServiceType $type): bool
    {
        return $user->can('service_catalog.view')
            && $user->organization_id === $type->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('service_catalog.create');
    }

    public function update(User $user, ServiceType $type): bool
    {
        return $user->can('service_catalog.update')
            && $user->organization_id === $type->organization_id;
    }

    public function delete(User $user, ServiceType $type): bool
    {
        return $user->can('service_catalog.delete')
            && $user->organization_id === $type->organization_id;
    }
}
