<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\CloudResource;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteCloudResource
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(CloudResource $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('cloud_resource.deleted', $model, $snapshot, null);
            ActivityHelper::log('CloudResource deleted', $model);
        });
    }
}
