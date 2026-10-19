<?php

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\VendorBill;
use App\Models\User;

class VendorBillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('financial_entries.view');
    }

    public function view(User $user, VendorBill $bill): bool
    {
        return $this->viewAny($user)
            && $user->organization_id === $bill->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('financial_entries.create');
    }

    public function update(User $user, VendorBill $bill): bool
    {
        return $user->can('financial_entries.update')
            && $user->organization_id === $bill->organization_id;
    }

    public function delete(User $user, VendorBill $bill): bool
    {
        return $user->can('financial_entries.delete')
            && $user->organization_id === $bill->organization_id;
    }
}
