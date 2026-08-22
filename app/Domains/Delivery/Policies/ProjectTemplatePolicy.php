<?php

namespace App\Domains\Delivery\Policies;

use App\Domains\Delivery\Models\ProjectTemplate;
use App\Models\User;

class ProjectTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('project_templates.view');
    }

    public function view(User $user, ProjectTemplate $template): bool
    {
        return $user->can('project_templates.view')
            && $user->organization_id === $template->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('project_templates.create');
    }

    public function update(User $user, ProjectTemplate $template): bool
    {
        return $user->can('project_templates.update')
            && $user->organization_id === $template->organization_id;
    }

    public function delete(User $user, ProjectTemplate $template): bool
    {
        return $user->can('project_templates.delete')
            && $user->organization_id === $template->organization_id;
    }
}
