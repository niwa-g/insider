<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Models\Server;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;

use Illuminate\Support\Facades\DB;

class UpsertServer
{
    public function __construct(
        private AuditLogger $auditLogger
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(?Server $model, array $data): Server
    {
        return DB::transaction(function () use ($model, $data) {
            if ($model === null) {
                $model = Server::query()->create($data);
                $this->auditLogger->log('server.created', $model, null, $model->toArray());
                ActivityHelper::log('Server created', $model);
            } else {
                $old = $model->toArray();
                $model->update($data);
                $this->auditLogger->log('server.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Server updated', $model);
            }

            return $model->fresh();
        });
    }
}
