<?php

namespace App\Domains\Crm\Actions;

use App\Domains\Crm\Models\ClientGroup;
use App\Domains\Platform\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpsertClientGroup
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $clientIds
     */
    public function handle(?ClientGroup $group, array $data, array $clientIds = []): ClientGroup
    {
        return DB::transaction(function () use ($group, $data, $clientIds) {
            if ($group === null) {
                $group = ClientGroup::query()->create($data);
                $this->auditLogger->log('client_group.created', $group, null, $group->toArray());
            } else {
                $old = $group->toArray();
                $group->update($data);
                $this->auditLogger->log('client_group.updated', $group, $old, $group->fresh()->toArray());
            }

            $group->clients()->sync($clientIds);

            return $group->fresh('clients');
        });
    }
}
