<?php

namespace Database\Factories\Integrations;

use App\Domains\Integrations\Enums\SyncLogAction;
use App\Domains\Integrations\Enums\SyncLogStatus;
use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Models\IntegrationSyncLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IntegrationSyncLog> */
class IntegrationSyncLogFactory extends Factory
{
    protected $model = IntegrationSyncLog::class;

    public function definition(): array
    {
        return [
            'organization_id' => fn (array $attrs) => Integration::query()->find($attrs['integration_id'])?->organization_id,
            'integration_id' => Integration::factory(),
            'action' => SyncLogAction::Sync,
            'status' => SyncLogStatus::Succeeded,
            'message' => 'Completed successfully.',
            'meta' => ['imported' => 1],
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ];
    }
}
