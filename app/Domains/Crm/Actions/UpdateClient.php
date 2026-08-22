<?php

namespace App\Domains\Crm\Actions;

use App\Domains\Crm\Models\Client;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpdateClient
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>|null  $groupIds
     */
    public function handle(Client $client, array $data, ?array $groupIds = null): Client
    {
        return DB::transaction(function () use ($client, $data, $groupIds) {
            $old = $client->toArray();
            $client->update($data);

            if ($groupIds !== null) {
                $client->groups()->sync($groupIds);
            }

            $this->auditLogger->log('client.updated', $client, $old, $client->fresh()->toArray());
            ActivityHelper::log('Client updated', $client);

            return $client->fresh(['groups', 'contacts', 'accountManager']);
        });
    }
}
