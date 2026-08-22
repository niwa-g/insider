<?php

namespace App\Domains\Operations\Policies;

use App\Domains\Operations\Models\OpsNotification;
use App\Models\User;

class OpsNotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can("notifications.view");
    }

    public function view(User $user, OpsNotification $model): bool
    {
        return $user->can('notifications.view')
            && $user->organization_id === $model->organization_id
            && $user->id === $model->user_id;
    }

    public function update(User $user, OpsNotification $model): bool
    {
        return $user->can('notifications.update')
            && $user->organization_id === $model->organization_id
            && $user->id === $model->user_id;
    }

    public function markRead(User $user, OpsNotification $model): bool
    {
        return $user->can("notifications.update")
            && $user->organization_id === $model->organization_id
            && $user->id === $model->user_id;
    }
}
