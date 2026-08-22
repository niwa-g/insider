<?php

namespace App\Domains\Integrations\Actions;

use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Enums\SyncLogAction;
use App\Domains\Integrations\Enums\SyncLogStatus;
use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Models\IntegrationSyncLog;
use App\Domains\Platform\Services\AuditLogger;
use App\Models\User;
use App\Support\ActivityHelper;

class DisconnectIntegration
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Integration $integration, User $user): Integration
    {
        $old = $integration->toPublicArray();

        $integration->update([
            'status' => IntegrationStatus::Disconnected,
            'connected_by' => null,
            'last_error' => null,
        ]);

        IntegrationSyncLog::query()->create([
            'organization_id' => $integration->organization_id,
            'integration_id' => $integration->id,
            'action' => SyncLogAction::Disconnect,
            'status' => SyncLogStatus::Succeeded,
            'message' => 'Disconnected.',
            'started_at' => now(),
            'finished_at' => now(),
            'triggered_by' => $user->id,
        ]);

        $this->auditLogger->log('integration.disconnected', $integration, $old, $integration->toPublicArray());
        ActivityHelper::log('Integration disconnected', $integration);

        return $integration->fresh();
    }
}
