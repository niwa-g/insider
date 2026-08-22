<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\ServiceCategory;
use App\Models\User;

class ServiceCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('service_catalog.view');
    }

    public function view(User $user, ServiceCategory $category): bool
    {
        return $user->can('service_catalog.view')
            && $user->organization_id === $category->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('service_catalog.create');
    }

    public function update(User $user, ServiceCategory $category): bool
    {
        return $user->can('service_catalog.update')
            && $user->organization_id === $category->organization_id;
    }

    public function delete(User $user, ServiceCategory $category): bool
    {
        return $user->can('service_catalog.delete')
            && $user->organization_id === $category->organization_id;
    }
}
