<?php

namespace App\Domains\Crm\Actions;

use App\Domains\Crm\Models\Client;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class CreateClient
{
    public function __construct(private AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $groupIds
     */
    public function handle(array $data, array $groupIds = []): Client
    {
        return DB::transaction(function () use ($data, $groupIds) {
            $client = Client::query()->create($data);

            if ($groupIds !== []) {
                $client->groups()->sync($groupIds);
            }

            $this->auditLogger->log('client.created', $client, null, $client->toArray());
            ActivityHelper::log('Client created', $client, [
                'company_name' => $client->company_name,
            ]);

            return $client->fresh(['groups', 'contacts', 'accountManager']);
        });
    }
}
