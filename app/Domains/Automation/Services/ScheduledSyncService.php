<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Actions\RecordAutomationRun;
use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Models\AutomationSchedule;
use App\Domains\Identity\Models\Organization;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Enums\SyncLogStatus;
use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Services\IntegrationSyncService;
use App\Models\User;

class ScheduledSyncService
{
    public function __construct(
        private IntegrationSyncService $syncService,
        private AutomationRuleEvaluator $evaluator,
        private RecordAutomationRun $recordRun,
    ) {}

    /** @return array{organizations: int, synced: int, failed: int} */
    public function syncAll(?AutomationSchedule $schedule = null, ?User $triggeredBy = null): array
    {
        $orgs = Organization::query()->pluck('id');
        $synced = 0;
        $failed = 0;

        foreach ($orgs as $orgId) {
            $result = $this->syncOrganization($orgId, $schedule, $triggeredBy);
            $synced += $result['synced'];
            $failed += $result['failed'];
        }

        return [
            'organizations' => $orgs->count(),
            'synced' => $synced,
            'failed' => $failed,
        ];
    }

    /** @return array{synced: int, failed: int} */
    public function syncOrganization(
        string $organizationId,
        ?AutomationSchedule $schedule = null,
        ?User $triggeredBy = null,
    ): array {
        $integrations = Integration::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('is_enabled', true)
            ->where('status', IntegrationStatus::Connected)
            ->get();

        $run = $this->recordRun->start(
            $organizationId,
            AutomationTrigger::IntegrationSyncFailed,
            schedule: $schedule,
            triggeredBy: $triggeredBy,
        );

        $synced = 0;
        $failed = 0;

        try {
            foreach ($integrations as $integration) {
                $log = $this->syncService->sync($integration, $triggeredBy);

                if ($log->status === SyncLogStatus::Succeeded) {
                    $synced++;
                } else {
                    $failed++;
                    $this->evaluator->evaluate(
                        $organizationId,
                        AutomationTrigger::IntegrationSyncFailed,
                        [
                            'integration_id' => $integration->id,
                            'title' => "Integration sync failed: {$integration->name}",
                            'body' => $log->message,
                            'notifiable' => $integration,
                        ],
                    );
                }
            }

            $status = $failed > 0 ? AutomationRunStatus::Failed : AutomationRunStatus::Succeeded;
            $this->recordRun->finish($run, $status, "Synced {$synced}, failed {$failed}.", [
                'synced_count' => $synced,
                'failed_count' => $failed,
            ]);
        } catch (\Throwable $e) {
            $this->recordRun->finish($run, AutomationRunStatus::Failed, $e->getMessage());

            throw $e;
        }

        return ['synced' => $synced, 'failed' => $failed];
    }
}
