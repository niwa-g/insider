<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Domain;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use App\Domains\Assets\Actions\SyncRenewalFromAsset;
use Illuminate\Support\Facades\DB;

class UpsertDomain
{
    public function __construct(
        private AuditLogger $auditLogger,
        private SyncRenewalFromAsset $renewalSync,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(?Domain $model, array $data): Domain
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = Domain::query()->create($data);
                $this->auditLogger->log('domain.created', $model, null, $model->toArray());
                ActivityHelper::log('Domain created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('domain.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Domain updated', $model);
            }
            $this->renewalSync->handle($model->fresh());

            return $model->fresh();
        });
    }
}
