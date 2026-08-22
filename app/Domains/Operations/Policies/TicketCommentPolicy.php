<?php

namespace App\Domains\Operations\Policies;

use App\Domains\Operations\Models\TicketComment;
use App\Models\User;

class TicketCommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can("tickets.view");
    }

    public function view(User $user, TicketComment $model): bool
    {
        return $user->can("tickets.view")
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('tickets.update');
    }

    public function update(User $user, TicketComment $model): bool
    {
        return $user->can("tickets.update")
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, TicketComment $model): bool
    {
        return $user->can("tickets.delete")
            && $user->organization_id === $model->organization_id;
    }
}
