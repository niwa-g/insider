<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Catalog\Enums\BillingCycle;
use App\Domains\Catalog\Enums\ClientServiceStatus;
use App\Domains\Catalog\Models\ClientService;
use App\Domains\Finance\Enums\RenewalStatus;
use App\Domains\Finance\Models\Renewal;
use App\Domains\Finance\Models\RenewalEvent;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarkRenewalRenewed
{
    public function __construct(
        private AuditLogger $auditLogger,
        private SyncRenewalFromClientService $syncRenewal,
    ) {}

    public function handle(Renewal $renewal, ?string $notes = null): Renewal
    {
        return DB::transaction(function () use ($renewal, $notes) {
            $old = $renewal->toArray();
            $renewable = $renewal->renewable;

            if ($renewable instanceof ClientService) {
                $nextDate = $this->nextRenewalDate($renewable);
                $renewable->update([
                    'renewal_date' => $nextDate,
                    'start_date' => $renewable->renewal_date ?? $renewable->start_date,
                    'status' => ClientServiceStatus::Active,
                ]);
                $this->syncRenewal->handle($renewable->fresh());
            } else {
                $renewal->update(['status' => RenewalStatus::Renewed]);
            }

            RenewalEvent::query()->create([
                'renewal_id' => $renewal->id,
                'user_id' => Auth::id(),
                'event' => 'renewed',
                'notes' => $notes,
                'created_at' => now(),
            ]);

            $fresh = $renewal->fresh();
            $this->auditLogger->log('renewal.renewed', $renewal, $old, $fresh?->toArray());
            ActivityHelper::log('Renewal marked as renewed', $renewal, ['title' => $renewal->title]);

            return $fresh ?? $renewal;
        });
    }

    private function nextRenewalDate(ClientService $service): string
    {
        $from = $service->renewal_date?->copy() ?? now();

        return match ($service->billing_cycle) {
            BillingCycle::Monthly => $from->addMonth()->toDateString(),
            BillingCycle::Quarterly => $from->addMonths(3)->toDateString(),
            BillingCycle::SemiAnnual => $from->addMonths(6)->toDateString(),
            BillingCycle::Yearly => $from->addYear()->toDateString(),
            default => $from->addYear()->toDateString(),
        };
    }
}
