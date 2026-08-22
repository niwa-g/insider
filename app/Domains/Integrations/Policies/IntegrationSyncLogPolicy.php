<?php

namespace App\Domains\Integrations\Policies;

use App\Domains\Integrations\Models\IntegrationSyncLog;
use App\Models\User;

class IntegrationSyncLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('integrations.view');
    }

    public function view(User $user, IntegrationSyncLog $model): bool
    {
        return $user->can('integrations.view')
            && $user->organization_id === $model->organization_id;
    }
}
