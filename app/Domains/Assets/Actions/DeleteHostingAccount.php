<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\HostingAccount;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteHostingAccount
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(HostingAccount $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('hosting_account.deleted', $model, $snapshot, null);
            ActivityHelper::log('HostingAccount deleted', $model);
        });
    }
}
