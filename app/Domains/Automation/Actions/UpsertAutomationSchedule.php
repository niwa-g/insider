<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Models\AutomationSchedule;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertAutomationSchedule
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data */
    public function handle(?AutomationSchedule $model, array $data): AutomationSchedule
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = AutomationSchedule::query()->create($data);
                $this->auditLogger->log('automation_schedule.created', $model, null, $model->toArray());
                ActivityHelper::log('Automation schedule created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('automation_schedule.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Automation schedule updated', $model);
            }

            return $model->fresh();
        });
    }
}
