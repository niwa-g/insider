<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\HostingAccount;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use App\Domains\Assets\Actions\SyncRenewalFromAsset;
use Illuminate\Support\Facades\DB;

class UpsertHostingAccount
{
    public function __construct(
        private AuditLogger $auditLogger,
        private SyncRenewalFromAsset $renewalSync,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(?HostingAccount $model, array $data): HostingAccount
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = HostingAccount::query()->create($data);
                $this->auditLogger->log('hosting_account.created', $model, null, $model->toArray());
                ActivityHelper::log('HostingAccount created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('hosting_account.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('HostingAccount updated', $model);
            }
            $this->renewalSync->handle($model->fresh());

            return $model->fresh();
        });
    }
}
