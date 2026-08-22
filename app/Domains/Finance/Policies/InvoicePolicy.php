<?php

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invoices.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.view')
            && $user->organization_id === $invoice->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('invoices.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.update')
            && $user->organization_id === $invoice->organization_id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.delete')
            && $user->organization_id === $invoice->organization_id;
    }
}
