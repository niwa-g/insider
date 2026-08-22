<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Environment;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;

use Illuminate\Support\Facades\DB;

class UpsertEnvironment
{
    public function __construct(
        private AuditLogger $auditLogger
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(?Environment $model, array $data): Environment
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = Environment::query()->create($data);
                $this->auditLogger->log('environment.created', $model, null, $model->toArray());
                ActivityHelper::log('Environment created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('environment.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Environment updated', $model);
            }

            return $model->fresh();
        });
    }
}
