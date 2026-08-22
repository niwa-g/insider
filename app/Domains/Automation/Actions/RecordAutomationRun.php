<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Models\AutomationRun;
use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Automation\Models\AutomationSchedule;
use App\Models\User;

class RecordAutomationRun
{
    /** @param  array<string, mixed>|null  $meta */
    public function start(
        string $organizationId,
        AutomationTrigger $trigger,
        ?AutomationRule $rule = null,
        ?AutomationSchedule $schedule = null,
        ?User $triggeredBy = null,
    ): AutomationRun {
        return AutomationRun::query()->create([
            'organization_id' => $organizationId,
            'automation_rule_id' => $rule?->id,
            'automation_schedule_id' => $schedule?->id,
            'trigger' => $trigger,
            'status' => AutomationRunStatus::Running,
            'started_at' => now(),
            'triggered_by' => $triggeredBy?->id,
        ]);
    }

    /** @param  array<string, mixed>|null  $meta */
    public function finish(
        AutomationRun $run,
        AutomationRunStatus $status,
        ?string $message = null,
        ?array $meta = null,
    ): AutomationRun {
        $run->update([
            'status' => $status,
            'message' => $message,
            'meta' => $meta,
            'finished_at' => now(),
        ]);

        if ($run->automation_rule_id) {
            AutomationRule::withoutGlobalScopes()
                ->where('id', $run->automation_rule_id)
                ->update([
                    'last_run_at' => now(),
                    'last_status' => $status,
                ]);
        }

        if ($run->automation_schedule_id) {
            AutomationSchedule::withoutGlobalScopes()
                ->where('id', $run->automation_schedule_id)
                ->update([
                    'last_run_at' => now(),
                    'last_status' => $status,
                    'last_message' => $message,
                ]);
        }

        return $run->fresh();
    }
}
