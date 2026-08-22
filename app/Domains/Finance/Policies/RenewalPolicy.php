<?php

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\Renewal;
use App\Models\User;

class RenewalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('renewals.view');
    }

    public function view(User $user, Renewal $renewal): bool
    {
        return $user->can('renewals.view')
            && $user->organization_id === $renewal->organization_id;
    }

    public function update(User $user, Renewal $renewal): bool
    {
        return $user->can('renewals.update')
            && $user->organization_id === $renewal->organization_id;
    }
}
