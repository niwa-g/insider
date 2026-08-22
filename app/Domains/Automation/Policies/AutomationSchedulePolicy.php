<?php

namespace App\Domains\Automation\Policies;

use App\Domains\Automation\Models\AutomationSchedule;
use App\Models\User;

class AutomationSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('automation.view');
    }

    public function view(User $user, AutomationSchedule $model): bool
    {
        return $user->can('automation.view')
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('automation.create');
    }

    public function update(User $user, AutomationSchedule $model): bool
    {
        return $user->can('automation.update')
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, AutomationSchedule $model): bool
    {
        return $user->can('automation.delete')
            && $user->organization_id === $model->organization_id;
    }

    public function run(User $user, AutomationSchedule $model): bool
    {
        return $user->can('automation.run')
            && $user->organization_id === $model->organization_id;
    }
}
