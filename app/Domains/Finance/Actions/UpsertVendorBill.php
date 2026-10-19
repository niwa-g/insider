<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\VendorBillStatus;
use App\Domains\Finance\Models\VendorBill;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertVendorBill
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array<string, mixed>  $data */
    public function handle(?VendorBill $bill, array $data): VendorBill
    {
        return DB::transaction(function () use ($bill, $data) {
            if ($bill === null) {
                $data['number'] = $data['number'] ?? $this->nextNumber($data['organization_id']);
                $data['status'] = $data['status'] ?? VendorBillStatus::Open->value;
                $bill = VendorBill::query()->create($data);
                $this->auditLogger->log('vendor_bill.created', $bill, null, $bill->toArray());
                ActivityHelper::log('Vendor bill created', $bill);

                return $bill;
            }

            if (in_array($bill->status, [VendorBillStatus::Paid, VendorBillStatus::Void], true)) {
                throw new \InvalidArgumentException('Paid or void bills cannot be edited.');
            }

            $old = $bill->toArray();
            $bill->update($data);
            $this->auditLogger->log('vendor_bill.updated', $bill, $old, $bill->fresh()->toArray());
            ActivityHelper::log('Vendor bill updated', $bill);

            return $bill->fresh();
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
