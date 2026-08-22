<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\CloudResource;
use App\Domains\Assets\Models\Environment;
use App\Domains\Assets\Models\EnvironmentResource;
use App\Domains\Assets\Models\HostingAccount;
use App\Domains\Assets\Models\Server;
use App\Domains\Platform\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SyncEnvironmentResources
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<int, array{type: string, id: string, role?: string|null, notes?: string|null}>  $resources
     */
    public function handle(Environment $environment, array $resources): void
    {
        DB::transaction(function () use ($environment, $resources) {
            $typeMap = [
                'server' => Server::class,
                'hosting_account' => HostingAccount::class,
                'cloud_resource' => CloudResource::class,
            ];

            $desired = collect($resources)->map(function (array $item) use ($typeMap, $environment) {
                $class = $typeMap[$item['type']] ?? null;
                if (! $class) {
                    return null;
                }

                /** @var Model|null $model */
                $model = $class::query()->find($item['id']);
                if (! $model || $model->organization_id !== $environment->organization_id) {
                    return null;
                }

                return [
                    'resourceable_type' => $class,
                    'resourceable_id' => $model->id,
                    'role' => $item['role'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ];
            })->filter()->values();

            $existing = $environment->resources()->get();
            $existingKeys = $existing->map(fn ($r) => $r->resourceable_type.':'.$r->resourceable_id)->all();
            $desiredKeys = $desired->map(fn ($r) => $r['resourceable_type'].':'.$r['resourceable_id'])->all();

            foreach ($existing as $resource) {
                $key = $resource->resourceable_type.':'.$resource->resourceable_id;
                if (! in_array($key, $desiredKeys, true)) {
                    $resource->delete();
                }
            }

            foreach ($desired as $item) {
                EnvironmentResource::query()->updateOrCreate(
                    [
                        'environment_id' => $environment->id,
                        'resourceable_type' => $item['resourceable_type'],
                        'resourceable_id' => $item['resourceable_id'],
                    ],
                    [
                        'organization_id' => $environment->organization_id,
                        'role' => $item['role'],
                        'notes' => $item['notes'],
                    ]
                );
            }

            $this->auditLogger->log('environment.resources.synced', $environment, null, [
                'resource_count' => count($desiredKeys),
            ]);
        });
    }
}
