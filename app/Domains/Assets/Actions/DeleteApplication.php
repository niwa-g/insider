<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Application;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteApplication
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Application $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('application.deleted', $model, $snapshot, null);
            ActivityHelper::log('Application deleted', $model);
        });
    }
}
