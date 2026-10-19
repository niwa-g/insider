<?php

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\DiscountReason;
use App\Models\User;

class DiscountReasonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invoices.view') || $user->can('invoices.update');
    }

    public function view(User $user, DiscountReason $reason): bool
    {
        return $this->viewAny($user)
            && $user->organization_id === $reason->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('invoices.update') || $user->can('invoices.create');
    }

    public function update(User $user, DiscountReason $reason): bool
    {
        return ($user->can('invoices.update') || $user->can('invoices.create'))
            && $user->organization_id === $reason->organization_id;
    }

    public function delete(User $user, DiscountReason $reason): bool
    {
        return $user->can('invoices.update')
            && $user->organization_id === $reason->organization_id
            && ! $reason->is_system;
    }
}
