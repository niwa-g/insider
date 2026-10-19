<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Finance\Enums\FinancialEntryCategory;
use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Enums\FinancialEntryType;
use App\Domains\Finance\Enums\VendorBillStatus;
use App\Domains\Finance\Models\FinancialEntry;
use App\Domains\Finance\Models\Renewal;
use App\Domains\Finance\Models\VendorBill;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateVendorBillFromClientService
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(ClientService $service, ?Renewal $renewal = null): ?VendorBill
    {
        if (! $service->vendor_id || (float) $service->cost <= 0) {
            return null;
        }

        return DB::transaction(function () use ($service, $renewal) {
            $vendor = $service->vendor;
            $bill = VendorBill::query()->create([
                'organization_id' => $service->organization_id,
                'vendor_id' => $service->vendor_id,
                'client_id' => $service->client_id,
                'client_service_id' => $service->id,
                'renewal_id' => $renewal?->id,
                'number' => $this->nextNumber($service->organization_id),
                'status' => VendorBillStatus::Open,
                'description' => "Vendor cost for {$service->name}",
                'amount' => $service->cost,
                'currency' => 'USD',
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'notes' => $vendor ? "Payable to {$vendor->name}" : null,
            ]);

            $entry = FinancialEntry::query()->create([
                'organization_id' => $service->organization_id,
                'client_id' => $service->client_id,
                'client_service_id' => $service->id,
                'entry_type' => FinancialEntryType::Cost,
                'category' => FinancialEntryCategory::Hosting,
                'vendor' => $vendor?->name,
                'vendor_id' => $service->vendor_id,
                'vendor_bill_id' => $bill->id,
                'description' => "Cost: {$service->name}".($vendor ? " ({$vendor->name})" : ''),
                'amount' => $service->cost,
                'currency' => 'USD',
                'occurred_on' => now()->toDateString(),
                'status' => FinancialEntryStatus::Posted,
                'created_by' => Auth::id(),
                'notes' => 'Auto-posted when service was renewed.',
            ]);

            $this->auditLogger->log('vendor_bill.created', $bill, null, $bill->toArray());
            $this->auditLogger->log('financial_entry.created', $entry, null, $entry->toArray());
            ActivityHelper::log('Vendor bill created from service renewal', $bill, [
                'client_service_id' => $service->id,
                'financial_entry_id' => $entry->id,
            ]);

            return $bill;
        });
    }

    private function nextNumber(string $organizationId): string
    {
        $last = VendorBill::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->orderByDesc('number')
            ->value('number');

        $seq = 1;
        if ($last && preg_match('/VB-(\d+)/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('VB-%04d', $seq);
    }
}
