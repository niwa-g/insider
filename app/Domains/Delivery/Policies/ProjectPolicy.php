<?php

namespace App\Domains\Delivery\Policies;

use App\Domains\Delivery\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('projects.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('projects.view')
            && $user->organization_id === $project->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('projects.update')
            && $user->organization_id === $project->organization_id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('projects.delete')
            && $user->organization_id === $project->organization_id;
    }
}
