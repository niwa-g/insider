<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Application;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;

use Illuminate\Support\Facades\DB;

class UpsertApplication
{
    public function __construct(
        private AuditLogger $auditLogger
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(?Application $model, array $data): Application
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = Application::query()->create($data);
                $this->auditLogger->log('application.created', $model, null, $model->toArray());
                ActivityHelper::log('Application created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('application.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Application updated', $model);
            }

            return $model->fresh();
        });
    }
}
