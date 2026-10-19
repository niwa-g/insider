<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\InvoicePayment;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordInvoicePayment
{
    public function __construct(
        private AuditLogger $auditLogger,
        private PostInvoiceRevenue $postInvoiceRevenue,
    ) {}

    /** @param  array{amount: float|string, method?: string, paid_on?: string, reference?: ?string, notes?: ?string}  $data */
    public function handle(Invoice $invoice, array $data): InvoicePayment
    {
        if (in_array($invoice->status, [InvoiceStatus::Draft, InvoiceStatus::Void, InvoiceStatus::Paid], true)) {
            throw new InvalidArgumentException('Payments can only be recorded on sent or overdue invoices.');
        }

        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $paidSoFar = (float) $invoice->payments()->sum('amount');
        $balance = round((float) $invoice->total - $paidSoFar, 2);

        if ($amount > $balance + 0.001) {
            throw new InvalidArgumentException("Payment exceeds balance due ({$balance}).");
        }

        return DB::transaction(function () use ($invoice, $data, $amount, $paidSoFar) {
            $payment = InvoicePayment::query()->create([
                'organization_id' => $invoice->organization_id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => $data['method'] ?? PaymentMethod::Other->value,
                'paid_on' => $data['paid_on'] ?? now()->toDateString(),
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            $newPaid = round($paidSoFar + $amount, 2);
            $old = $invoice->toArray();

            if ($newPaid + 0.001 >= (float) $invoice->total) {
                $invoice->update([
                    'status' => InvoiceStatus::Paid,
                    'paid_at' => now(),
                ]);
                $this->postInvoiceRevenue->handle($invoice->fresh());
            }

            $this->auditLogger->log('invoice.payment_recorded', $invoice, $old, $invoice->fresh()?->toArray());
            ActivityHelper::log('Invoice payment recorded', $invoice, [
                'payment_id' => $payment->id,
                'amount' => $amount,
            ]);

            return $payment;
        });
    }
}
