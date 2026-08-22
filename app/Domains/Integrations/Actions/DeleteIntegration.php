<?php

namespace App\Domains\Integrations\Actions;

use App\Domains\Integrations\Models\Integration;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteIntegration
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Integration $model): void
    {
        DB::transaction(function () use ($model): void {
            $old = $model->toPublicArray();
            $model->delete();
            $this->auditLogger->log('integration.deleted', $model, $old, null);
            ActivityHelper::log('Integration deleted', $model);
        });
    }
}
