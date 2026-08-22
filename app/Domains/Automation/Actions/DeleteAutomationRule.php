<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteAutomationRule
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(AutomationRule $model): void
    {
        DB::transaction(function () use ($model) {
            $old = $model->toArray();
            $model->delete();
            $this->auditLogger->log('automation_rule.deleted', $model, $old, null);
            ActivityHelper::log('Automation rule deleted', $model);
        });
    }
}
