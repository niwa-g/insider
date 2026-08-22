<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\CloudResource;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;

use Illuminate\Support\Facades\DB;

class UpsertCloudResource
{
    public function __construct(
        private AuditLogger $auditLogger
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(?CloudResource $model, array $data): CloudResource
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = CloudResource::query()->create($data);
                $this->auditLogger->log('cloud_resource.created', $model, null, $model->toArray());
                ActivityHelper::log('CloudResource created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('cloud_resource.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('CloudResource updated', $model);
            }

            return $model->fresh();
        });
    }
}
