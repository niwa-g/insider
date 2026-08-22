<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Models\AutomationSchedule;
use App\Support\ActivityHelper;

class ToggleAutomationSchedule
{
    public function handle(AutomationSchedule $model): AutomationSchedule
    {
        $model->update(['is_enabled' => ! $model->is_enabled]);
        ActivityHelper::log($model->is_enabled ? 'Automation schedule enabled' : 'Automation schedule disabled', $model);

        return $model->fresh();
    }
}
