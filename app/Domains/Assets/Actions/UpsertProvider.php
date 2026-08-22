<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Provider;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;

use Illuminate\Support\Facades\DB;

class UpsertProvider
{
    public function __construct(
        private AuditLogger $auditLogger
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(?Provider $model, array $data): Provider
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = Provider::query()->create($data);
                $this->auditLogger->log('provider.created', $model, null, $model->toArray());
                ActivityHelper::log('Provider created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('provider.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Provider updated', $model);
            }

            return $model->fresh();
        });
    }
}
