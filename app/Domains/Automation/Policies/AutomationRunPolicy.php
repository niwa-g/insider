<?php

namespace App\Domains\Automation\Policies;

use App\Domains\Automation\Models\AutomationRun;
use App\Models\User;

class AutomationRunPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('automation.view');
    }

    public function view(User $user, AutomationRun $model): bool
    {
        return $user->can('automation.view')
            && $user->organization_id === $model->organization_id;
    }
}
