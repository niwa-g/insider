<?php

namespace App\Domains\Integrations\Actions;

use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Enums\SyncLogAction;
use App\Domains\Integrations\Enums\SyncLogStatus;
use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Models\IntegrationSyncLog;
use App\Domains\Integrations\Services\IntegrationSyncService;
use App\Domains\Platform\Services\AuditLogger;
use App\Models\User;
use App\Support\ActivityHelper;

class ConnectIntegration
{
    public function __construct(
        private AuditLogger $auditLogger,
        private IntegrationSyncService $syncService,
    ) {}

    public function handle(Integration $integration, User $user): Integration
    {
        $log = IntegrationSyncLog::query()->create([
            'organization_id' => $integration->organization_id,
            'integration_id' => $integration->id,
            'action' => SyncLogAction::Connect,
            'status' => SyncLogStatus::Running,
            'started_at' => now(),
            'triggered_by' => $user->id,
        ]);

        $testLog = $this->syncService->testConnection($integration, $user);

        if ($testLog->status === SyncLogStatus::Failed) {
            $log->update([
                'status' => SyncLogStatus::Failed,
                'message' => $testLog->message,
                'finished_at' => now(),
            ]);

            return $integration->fresh();
        }

        $integration->update([
            'status' => IntegrationStatus::Connected,
            'connected_by' => $user->id,
            'last_error' => null,
        ]);

        $log->update([
            'status' => SyncLogStatus::Succeeded,
            'message' => 'Connected successfully.',
            'finished_at' => now(),
        ]);

        $this->auditLogger->log('integration.connected', $integration, null, $integration->toPublicArray());
        ActivityHelper::log('Integration connected', $integration);

        return $integration->fresh();
    }
}
