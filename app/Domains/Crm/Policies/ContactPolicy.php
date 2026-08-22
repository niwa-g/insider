<?php

namespace App\Domains\Crm\Policies;

use App\Domains\Crm\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('contacts.view');
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->can('contacts.view')
            && $user->organization_id === $contact->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('contacts.create');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->can('contacts.update')
            && $user->organization_id === $contact->organization_id;
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->can('contacts.delete')
            && $user->organization_id === $contact->organization_id;
    }
}
