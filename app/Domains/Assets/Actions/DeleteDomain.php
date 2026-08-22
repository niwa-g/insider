<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Domain;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteDomain
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Domain $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('domain.deleted', $model, $snapshot, null);
            ActivityHelper::log('Domain deleted', $model);
        });
    }
}
