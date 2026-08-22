<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Provider;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteProvider
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Provider $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('provider.deleted', $model, $snapshot, null);
            ActivityHelper::log('Provider deleted', $model);
        });
    }
}
