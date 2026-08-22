<?php

namespace App\Domains\Automation\Jobs;

use App\Domains\Automation\Services\ScheduledSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncIntegrationsJob implements ShouldQueue
{
    use Queueable;

    public function handle(ScheduledSyncService $service): void
    {
        $service->syncAll();
    }
}
