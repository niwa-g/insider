<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\OpsNotification;
use App\Models\User;

class MarkAllNotificationsRead
{
    public function handle(User $user): int
    {
        return OpsNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
