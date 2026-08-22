<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertAutomationRule
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data */
    public function handle(?AutomationRule $model, array $data): AutomationRule
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = AutomationRule::query()->create($data);
                $this->auditLogger->log('automation_rule.created', $model, null, $model->toArray());
                ActivityHelper::log('Automation rule created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('automation_rule.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Automation rule updated', $model);
            }

            return $model->fresh();
        });
    }
}
