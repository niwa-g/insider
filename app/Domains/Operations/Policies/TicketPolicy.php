<?php

namespace App\Domains\Operations\Policies;

use App\Domains\Operations\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can("tickets.view");
    }

    public function view(User $user, Ticket $model): bool
    {
        return $user->can("tickets.view")
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can("tickets.create");
    }

    public function update(User $user, Ticket $model): bool
    {
        return $user->can("tickets.update")
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, Ticket $model): bool
    {
        return $user->can("tickets.delete")
            && $user->organization_id === $model->organization_id;
    }
}
