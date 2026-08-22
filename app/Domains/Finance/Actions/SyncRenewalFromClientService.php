<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Catalog\Enums\ClientServiceStatus;
use App\Domains\Catalog\Models\ClientService;
use App\Domains\Finance\Enums\RenewalStatus;
use App\Domains\Finance\Models\Renewal;
use Carbon\Carbon;

class SyncRenewalFromClientService
{
    public function handle(ClientService $service): ?Renewal
    {
        $existing = Renewal::query()
            ->where('renewable_type', ClientService::class)
            ->where('renewable_id', $service->id)
            ->first();

        $shouldTrack = $service->renewal_date
            && ! in_array($service->status, [
                ClientServiceStatus::Cancelled,
                ClientServiceStatus::Archived,
            ], true)
            && ! $service->trashed();

        if (! $shouldTrack) {
            if ($existing) {
                $existing->update(['status' => RenewalStatus::Cancelled]);
            }

            return $existing;
        }

        $status = $this->deriveStatus(Carbon::parse($service->renewal_date));

        $payload = [
            'organization_id' => $service->organization_id,
            'client_id' => $service->client_id,
            'renewable_type' => ClientService::class,
            'renewable_id' => $service->id,
            'title' => $service->name,
            'renewal_type' => 'service',
            'renewal_date' => $service->renewal_date,
            'cost' => $service->cost,
            'selling_price' => $service->selling_price,
            'status' => $status,
            'owner_id' => $service->account_manager_id,
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return Renewal::query()->create($payload);
    }

    private function deriveStatus(Carbon $renewalDate): RenewalStatus
    {
        $today = now()->startOfDay();

        if ($renewalDate->lt($today)) {
            return RenewalStatus::Overdue;
        }

        if ($renewalDate->lte($today->copy()->addDays(7))) {
            return RenewalStatus::Due;
        }

        return RenewalStatus::Upcoming;
    }
}
