<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Server;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteServer
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Server $model): void
    {
        DB::transaction(function () use ($model) {
            $snapshot = $model->toArray();
            $model->delete();
            $this->auditLogger->log('server.deleted', $model, $snapshot, null);
            ActivityHelper::log('Server deleted', $model);
        });
    }
}
