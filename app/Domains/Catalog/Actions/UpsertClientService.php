<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Models\ClientService;
use App\Domains\Finance\Actions\SyncRenewalFromClientService;
use App\Domains\Finance\Models\Vendor;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertClientService
{
    public function __construct(
        private AuditLogger $auditLogger,
        private SyncRenewalFromClientService $syncRenewal,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(?ClientService $service, array $data): ClientService
    {
        return DB::transaction(function () use ($service, $data) {
            $data['recurring_revenue'] = in_array(
                $data['billing_type'] ?? null,
                [BillingType::Recurring, BillingType::Recurring->value],
                true
            ) ? ($data['selling_price'] ?? 0) : 0;

            if (! empty($data['vendor_id'])) {
                $vendorName = Vendor::query()->whereKey($data['vendor_id'])->value('name');
                if ($vendorName) {
                    $data['provider'] = $vendorName;
                }
            }

            if ($service === null) {
                $service = ClientService::query()->create($data);
                $this->auditLogger->log('client_service.created', $service, null, $service->toArray());
                ActivityHelper::log('Client service created', $service, ['name' => $service->name]);
            } else {
                $old = $service->toArray();
                $service->update($data);
                $this->auditLogger->log('client_service.updated', $service, $old, $service->fresh()->toArray());
                ActivityHelper::log('Client service updated', $service);
            }

            $this->syncRenewal->handle($service->fresh());

            return $service->fresh(['client', 'serviceType', 'accountManager', 'technicalOwner', 'vendor']);
        });
    }
}
