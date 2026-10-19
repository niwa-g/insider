<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Enums\VendorBillStatus;
use App\Domains\Finance\Models\VendorBill;
use App\Domains\Finance\Models\VendorBillPayment;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordVendorBillPayment
{
    public function __construct(private AuditLogger $auditLogger) {}

    /** @param  array{amount: float|string, method?: string, paid_on?: string, reference?: ?string, notes?: ?string}  $data */
    public function handle(VendorBill $bill, array $data): VendorBillPayment
    {
        if (in_array($bill->status, [VendorBillStatus::Paid, VendorBillStatus::Void], true)) {
            throw new InvalidArgumentException('Payments can only be recorded on open or partial bills.');
        }

        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $paidSoFar = (float) $bill->payments()->sum('amount');
        $balance = round((float) $bill->amount - $paidSoFar, 2);

        if ($amount > $balance + 0.001) {
            throw new InvalidArgumentException("Payment exceeds balance due ({$balance}).");
        }

        return DB::transaction(function () use ($bill, $data, $amount, $paidSoFar) {
            $payment = VendorBillPayment::query()->create([
                'organization_id' => $bill->organization_id,
                'vendor_bill_id' => $bill->id,
                'amount' => $amount,
                'method' => $data['method'] ?? PaymentMethod::Other->value,
                'paid_on' => $data['paid_on'] ?? now()->toDateString(),
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            $newPaid = round($paidSoFar + $amount, 2);
            $old = $bill->toArray();

            if ($newPaid + 0.001 >= (float) $bill->amount) {
                $bill->update([
                    'status' => VendorBillStatus::Paid,
                    'paid_at' => now(),
                ]);
            } else {
                $bill->update(['status' => VendorBillStatus::Partial]);
            }

            $this->auditLogger->log('vendor_bill.payment_recorded', $bill, $old, $bill->fresh()?->toArray());
            ActivityHelper::log('Vendor bill payment recorded', $bill, [
                'payment_id' => $payment->id,
                'amount' => $amount,
            ]);

            return $payment;
        });
    }
}
