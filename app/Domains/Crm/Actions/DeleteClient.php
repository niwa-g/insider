<?php

namespace App\Domains\Crm\Actions;

use App\Domains\Crm\Models\Client;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteClient
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Client $client): void
    {
        DB::transaction(function () use ($client) {
            $snapshot = $client->toArray();
            $client->delete();

            $this->auditLogger->log('client.deleted', $client, $snapshot, null);
            ActivityHelper::log('Client archived/deleted', $client, [
                'company_name' => $snapshot['company_name'] ?? null,
            ]);
        });
    }
}
