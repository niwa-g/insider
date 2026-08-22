<?php

namespace App\Domains\Operations\Policies;

use App\Domains\Operations\Models\SlaPolicy;
use App\Models\User;

class SlaPolicyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can("sla.view");
    }

    public function view(User $user, SlaPolicy $model): bool
    {
        return $user->can("sla.view")
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can("sla.create");
    }

    public function update(User $user, SlaPolicy $model): bool
    {
        return $user->can("sla.update")
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, SlaPolicy $model): bool
    {
        return $user->can("sla.delete")
            && $user->organization_id === $model->organization_id;
    }
}
