<?php

namespace App\Domains\Automation\Jobs;

use App\Domains\Automation\Actions\RunAutomationScheduleNow;
use App\Domains\Automation\Models\AutomationSchedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunDueAutomationSchedulesJob implements ShouldQueue
{
    use Queueable;

    public function handle(RunAutomationScheduleNow $runSchedule): void
    {
        $schedules = AutomationSchedule::withoutGlobalScopes()
            ->where('is_enabled', true)
            ->get();

        foreach ($schedules as $schedule) {
            if ($schedule->frequency->isDue($schedule->last_run_at)) {
                $runSchedule->handle($schedule);
            }
        }
    }
}
