<?php

namespace App\Domains\Delivery\Policies;

use App\Domains\Delivery\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('tasks.view')
            && $user->organization_id === $task->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.update')
            && $user->organization_id === $task->organization_id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.delete')
            && $user->organization_id === $task->organization_id;
    }
}
