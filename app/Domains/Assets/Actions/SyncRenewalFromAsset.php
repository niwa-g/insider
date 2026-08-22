<?php

namespace App\Domains\Assets\Actions;

use App\Domains\Assets\Enums\DomainStatus;
use App\Domains\Assets\Enums\HostingAccountStatus;
use App\Domains\Assets\Enums\SslCertificateStatus;
use App\Domains\Assets\Models\Domain;
use App\Domains\Assets\Models\HostingAccount;
use App\Domains\Assets\Models\SslCertificate;
use App\Domains\Finance\Enums\RenewalStatus;
use App\Domains\Finance\Models\Renewal;
use Carbon\Carbon;

class SyncRenewalFromAsset
{
    public function handle(Domain|SslCertificate|HostingAccount $asset): ?Renewal
    {
        $existing = Renewal::query()
            ->where('renewable_type', $asset::class)
            ->where('renewable_id', $asset->id)
            ->first();

        $expiresAt = $asset->expires_at ?? null;
        $cancelledStatuses = match (true) {
            $asset instanceof Domain => [DomainStatus::Cancelled],
            $asset instanceof SslCertificate => [SslCertificateStatus::Revoked],
            $asset instanceof HostingAccount => [HostingAccountStatus::Cancelled],
            default => [],
        };

        $shouldTrack = $expiresAt
            && ! in_array($asset->status, $cancelledStatuses, true)
            && (! method_exists($asset, 'trashed') || ! $asset->trashed());

        if (! $shouldTrack) {
            if ($existing) {
                $existing->update(['status' => RenewalStatus::Cancelled]);
            }

            return $existing;
        }

        $renewalType = match (true) {
            $asset instanceof Domain => 'domain',
            $asset instanceof SslCertificate => 'ssl',
            $asset instanceof HostingAccount => 'hosting',
            default => 'other',
        };

        $title = match (true) {
            $asset instanceof Domain => $asset->name,
            $asset instanceof SslCertificate => $asset->common_name,
            $asset instanceof HostingAccount => $asset->name,
            default => 'Renewal',
        };

        $cost = match (true) {
            $asset instanceof HostingAccount => $asset->cost,
            default => 0,
        };

        $payload = [
            'organization_id' => $asset->organization_id,
            'client_id' => $asset->client_id,
            'renewable_type' => $asset::class,
            'renewable_id' => $asset->id,
            'title' => $title,
            'renewal_type' => $renewalType,
            'renewal_date' => $expiresAt,
            'cost' => $cost,
            'selling_price' => 0,
            'status' => $this->deriveStatus(Carbon::parse($expiresAt)),
            'auto_renew' => $asset->auto_renew ?? false,
            'owner_id' => null,
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
