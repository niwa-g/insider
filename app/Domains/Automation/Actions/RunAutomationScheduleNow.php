<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationScheduleJobType;
use App\Domains\Automation\Models\AutomationSchedule;
use App\Domains\Automation\Services\RenewalScanService;
use App\Domains\Automation\Services\ScheduledSyncService;
use App\Domains\Automation\Services\SlaScanService;
use App\Models\User;

class RunAutomationScheduleNow
{
    public function __construct(
        private RenewalScanService $renewalScan,
        private SlaScanService $slaScan,
        private ScheduledSyncService $syncService,
        private RecordAutomationRun $recordRun,
    ) {}

    /** @return array<string, mixed> */
    public function handle(AutomationSchedule $schedule, ?User $triggeredBy = null): array
    {
        if (! $schedule->is_enabled) {
            $run = $this->recordRun->start(
                $schedule->organization_id,
                $schedule->job_type->toTrigger(),
                schedule: $schedule,
                triggeredBy: $triggeredBy,
            );
            $this->recordRun->finish($run, AutomationRunStatus::Skipped, 'Schedule is disabled.');

            return ['status' => 'skipped'];
        }

        return match ($schedule->job_type) {
            AutomationScheduleJobType::ScanRenewals => $this->renewalScan->scanOrganization(
                $schedule->organization_id,
                $schedule,
                $triggeredBy,
            ),
            AutomationScheduleJobType::ScanSla => $this->slaScan->scanOrganization(
                $schedule->organization_id,
                $schedule,
                $triggeredBy,
            ),
            AutomationScheduleJobType::SyncIntegrations => $this->syncService->syncOrganization(
                $schedule->organization_id,
                $schedule,
                $triggeredBy,
            ),
            AutomationScheduleJobType::EvaluateRules => ['status' => 'succeeded'],
        };
    }
}
