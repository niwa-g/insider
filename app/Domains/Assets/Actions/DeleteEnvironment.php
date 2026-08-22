<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Environment;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteEnvironment
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Environment $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('environment.deleted', $model, $snapshot, null);
            ActivityHelper::log('Environment deleted', $model);
        });
    }
}
