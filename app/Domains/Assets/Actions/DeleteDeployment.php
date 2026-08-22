<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Deployment;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteDeployment
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Deployment $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('deployment.deleted', $model, $snapshot, null);
            ActivityHelper::log('Deployment deleted', $model);
        });
    }
}
