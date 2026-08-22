<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\SlaPolicy;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteSlaPolicy
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(SlaPolicy $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->targets()->delete();
            $model->delete();
            $this->auditLogger->log('sla_policy.deleted', $model, $snapshot, null);
            ActivityHelper::log('SLA policy deleted', $model);
        });
    }
}
