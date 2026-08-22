<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Finance\Enums\RenewalStatus;
use App\Domains\Finance\Models\Renewal;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class DeleteClientService
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(ClientService $service): void
    {
        DB::transaction(function () use ($service) {
            $snapshot = $service->toArray();

            Renewal::query()
                ->where('renewable_type', ClientService::class)
                ->where('renewable_id', $service->id)
                ->update(['status' => RenewalStatus::Cancelled]);

            $service->delete();

            $this->auditLogger->log('client_service.deleted', $service, $snapshot, null);
            ActivityHelper::log('Client service deleted', $service, ['name' => $snapshot['name'] ?? null]);
        });
    }
}
