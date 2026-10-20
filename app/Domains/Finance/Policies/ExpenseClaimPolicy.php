<?php

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Enums\ExpenseClaimStatus;
use App\Domains\Finance\Models\ExpenseClaim;
use App\Models\User;

class ExpenseClaimPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('financial_entries.view');
    }

    public function view(User $user, ExpenseClaim $claim): bool
    {
        return $user->can('financial_entries.view')
            && ($user->organization_id === $claim->organization_id)
            && ($user->can('financial_entries.create') || $user->can('financial_entries.update') || $claim->claimant_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can('financial_entries.create');
    }

    public function approve(User $user, ExpenseClaim $claim): bool
    {
        return $user->can('financial_entries.post')
            && $claim->status === ExpenseClaimStatus::Pending
            && $claim->claimant_id !== $user->id;
    }

    public function pay(User $user, ExpenseClaim $claim): bool
    {
        return $user->can('financial_entries.post')
            && $claim->status === ExpenseClaimStatus::Approved;
    }

    public function delete(User $user, ExpenseClaim $claim): bool
    {
        return $user->can('financial_entries.create')
            && $claim->claimant_id === $user->id
            && $claim->status === ExpenseClaimStatus::Pending;
    }
}
