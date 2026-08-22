<?php

namespace App\Domains\Integrations\Services;

use App\Domains\Assets\Enums\ServerRole;
use App\Domains\Assets\Enums\ServerStatus;
use App\Domains\Assets\Models\Server;
use App\Domains\Integrations\Contracts\CloudInventoryProviderInterface;
use App\Domains\Integrations\Contracts\DomainDnsProviderInterface;
use App\Domains\Integrations\Contracts\HostingProviderInterface;
use App\Domains\Integrations\Contracts\SourceControlProviderInterface;
use App\Domains\Integrations\Data\ExternalResourceDTO;
use App\Domains\Integrations\Enums\IntegrationCategory;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Enums\SyncLogAction;
use App\Domains\Integrations\Enums\SyncLogStatus;
use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Models\IntegrationExternalRef;
use App\Domains\Integrations\Models\IntegrationSyncLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class IntegrationSyncService
{
    public function __construct(
        private IntegrationAdapterFactory $adapterFactory,
    ) {}

    public function sync(Integration $integration, ?User $triggeredBy = null): IntegrationSyncLog
    {
        return $this->run($integration, SyncLogAction::Sync, $triggeredBy, function () use ($integration): array {
            $adapter = $this->adapterFactory->make($integration);
            $resources = $this->fetchResources($integration, $adapter);
            $imported = 0;

            foreach ($resources as $resource) {
                $ref = $this->upsertExternalRef($integration, $resource);
                $this->maybeLinkLocalAsset($integration, $ref, $resource);
                $imported++;
            }

            $integration->update([
                'status' => IntegrationStatus::Connected,
                'last_synced_at' => now(),
                'last_error' => null,
            ]);

            return ['imported' => $imported];
        });
    }

    public function testConnection(Integration $integration, ?User $triggeredBy = null): IntegrationSyncLog
    {
        return $this->run($integration, SyncLogAction::Test, $triggeredBy, function () use ($integration): array {
            $adapter = $this->adapterFactory->make($integration);
            $ok = $adapter->testConnection();

            if (! $ok) {
                throw new \RuntimeException('Connection test failed.');
            }

            return ['connected' => true];
        });
    }

    /** @param  callable(): array<string, mixed>  $callback */
    private function run(Integration $integration, SyncLogAction $action, ?User $triggeredBy, callable $callback): IntegrationSyncLog
    {
        $log = IntegrationSyncLog::query()->create([
            'organization_id' => $integration->organization_id,
            'integration_id' => $integration->id,
            'action' => $action,
            'status' => SyncLogStatus::Running,
            'started_at' => now(),
            'triggered_by' => $triggeredBy?->id,
        ]);

        $previousStatus = $integration->status;
        $integration->update(['status' => IntegrationStatus::Syncing]);

        try {
            $meta = DB::transaction(fn () => $callback());

            $log->update([
                'status' => SyncLogStatus::Succeeded,
                'message' => 'Completed successfully.',
                'meta' => $meta,
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            $integration->update([
                'status' => IntegrationStatus::Error,
                'last_error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => SyncLogStatus::Failed,
                'message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        } finally {
            if ($integration->fresh()->status === IntegrationStatus::Syncing) {
                $integration->update(['status' => $previousStatus === IntegrationStatus::Syncing
                    ? IntegrationStatus::Connected
                    : $previousStatus,
                ]);
            }
        }

        return $log->fresh();
    }

    /** @return array<int, ExternalResourceDTO> */
    private function fetchResources(
        Integration $integration,
        CloudInventoryProviderInterface|DomainDnsProviderInterface|HostingProviderInterface|SourceControlProviderInterface $adapter,
    ): array {
        return match ($integration->category) {
            IntegrationCategory::Cloud => $adapter instanceof CloudInventoryProviderInterface
                ? $adapter->fetchInventory()
                : [],
            IntegrationCategory::SourceControl => $adapter instanceof SourceControlProviderInterface
                ? $adapter->fetchRepositories()
                : [],
            IntegrationCategory::Dns, IntegrationCategory::Registrar => $adapter instanceof DomainDnsProviderInterface
                ? $this->fetchDnsResources($adapter)
                : [],
            IntegrationCategory::Hosting => $adapter instanceof HostingProviderInterface
                ? $adapter->fetchAccounts()
                : [],
        };
    }

    /** @return array<int, ExternalResourceDTO> */
    private function fetchDnsResources(DomainDnsProviderInterface $adapter): array
    {
        $resources = [];

        foreach ($adapter->fetchZones() as $zone) {
            $resources[] = $zone;

            foreach ($adapter->fetchDnsRecords($zone->externalId) as $record) {
                $resources[] = $record;
            }
        }

        return $resources;
    }

    private function upsertExternalRef(Integration $integration, ExternalResourceDTO $resource): IntegrationExternalRef
    {
        return IntegrationExternalRef::query()->updateOrCreate(
            [
                'integration_id' => $integration->id,
                'external_type' => $resource->externalType,
                'external_id' => $resource->externalId,
            ],
            [
                'organization_id' => $integration->organization_id,
                'external_name' => $resource->externalName,
                'payload' => $resource->payload,
                'last_seen_at' => now(),
            ],
        );
    }

    private function maybeLinkLocalAsset(
        Integration $integration,
        IntegrationExternalRef $ref,
        ExternalResourceDTO $resource,
    ): void {
        if ($ref->local_id) {
            return;
        }

        $local = match ($resource->externalType) {
            'ec2_instance' => $this->createServerStub($integration, $resource),
            default => null,
        };

        if ($local !== null) {
            $ref->update([
                'local_type' => $local::class,
                'local_id' => $local->id,
            ]);
        }
    }

    private function createServerStub(Integration $integration, ExternalResourceDTO $resource): Server
    {
        return Server::query()->create([
            'organization_id' => $integration->organization_id,
            'name' => $resource->externalName ?? $resource->externalId,
            'hostname' => ($resource->payload['region'] ?? 'unknown').'.compute.internal',
            'role' => ServerRole::Web,
            'status' => ServerStatus::Active,
            'notes' => 'Imported from integration '.$integration->name,
        ]);
    }
}
