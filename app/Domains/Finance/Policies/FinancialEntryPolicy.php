<?php

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\FinancialEntry;
use App\Models\User;

class FinancialEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('financial_entries.view');
    }

    public function view(User $user, FinancialEntry $entry): bool
    {
        return $user->can('financial_entries.view')
            && $user->organization_id === $entry->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('financial_entries.create');
    }

    public function update(User $user, FinancialEntry $entry): bool
    {
        return $user->can('financial_entries.update')
            && $user->organization_id === $entry->organization_id;
    }

    public function delete(User $user, FinancialEntry $entry): bool
    {
        return $user->can('financial_entries.delete')
            && $user->organization_id === $entry->organization_id;
    }

    public function post(User $user, FinancialEntry $entry): bool
    {
        return $user->can('financial_entries.post')
            && $user->organization_id === $entry->organization_id;
    }

    public function void(User $user, FinancialEntry $entry): bool
    {
        return $user->can('financial_entries.post')
            && $user->organization_id === $entry->organization_id;
    }
}
