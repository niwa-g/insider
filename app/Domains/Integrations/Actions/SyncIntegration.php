<?php

namespace App\Domains\Integrations\Actions;

use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Models\IntegrationSyncLog;
use App\Domains\Integrations\Services\IntegrationSyncService;
use App\Models\User;

class SyncIntegration
{
    public function __construct(private IntegrationSyncService $syncService) {}

    public function handle(Integration $integration, ?User $user = null): IntegrationSyncLog
    {
        return $this->syncService->sync($integration, $user);
    }
}
