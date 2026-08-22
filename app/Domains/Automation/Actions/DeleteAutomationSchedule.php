<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Models\AutomationSchedule;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteAutomationSchedule
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(AutomationSchedule $model): void
    {
        DB::transaction(function () use ($model) {
            $old = $model->toArray();
            $model->delete();
            $this->auditLogger->log('automation_schedule.deleted', $model, $old, null);
            ActivityHelper::log('Automation schedule deleted', $model);
        });
    }
}
