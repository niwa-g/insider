<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Deployment;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;

use Illuminate\Support\Facades\DB;

class UpsertDeployment
{
    public function __construct(
        private AuditLogger $auditLogger
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(?Deployment $model, array $data): Deployment
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = Deployment::query()->create($data);
                $this->auditLogger->log('deployment.created', $model, null, $model->toArray());
                ActivityHelper::log('Deployment created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('deployment.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Deployment updated', $model);
            }

            return $model->fresh();
        });
    }
}
