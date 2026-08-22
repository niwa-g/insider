<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\OpsNotification;
use Illuminate\Support\Facades\DB;

class MarkNotificationRead
{
    public function handle(OpsNotification $notification): OpsNotification
    {
        return DB::transaction(function () use ($notification) {
            if ($notification->read_at === null) {
                $notification->update(['read_at' => now()]);
            }

            return $notification->fresh();
        });
    }
}
