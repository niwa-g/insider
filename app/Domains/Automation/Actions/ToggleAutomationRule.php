<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Models\AutomationRule;
use App\Support\ActivityHelper;

class ToggleAutomationRule
{
    public function handle(AutomationRule $model): AutomationRule
    {
        $model->update(['is_enabled' => ! $model->is_enabled]);
        ActivityHelper::log($model->is_enabled ? 'Automation rule enabled' : 'Automation rule disabled', $model);

        return $model->fresh();
    }
}
