<?php

namespace App\Domains\Finance\Services;

use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Enums\ClientServiceStatus;
use App\Domains\Catalog\Models\ClientService;

class MrrCalculator
{
    public function forOrganization(string $organizationId, ?string $clientId = null): float
    {
        $services = ClientService::query()
            ->where('organization_id', $organizationId)
            ->where('billing_type', BillingType::Recurring)
            ->where('status', ClientServiceStatus::Active)
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->get(['recurring_revenue', 'selling_price']);

        return $services->sum(function (ClientService $service): float {
            $mrr = (float) $service->recurring_revenue;

            return $mrr > 0 ? $mrr : (float) $service->selling_price;
        });
    }
}
