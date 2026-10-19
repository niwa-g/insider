<?php

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\Vendor;
use App\Models\User;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('financial_entries.view');
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $this->viewAny($user)
            && $user->organization_id === $vendor->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('financial_entries.create');
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->can('financial_entries.update')
            && $user->organization_id === $vendor->organization_id;
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->can('financial_entries.delete')
            && $user->organization_id === $vendor->organization_id;
    }
}
