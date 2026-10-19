<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Enums\FinancialEntryType;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Models\FinancialEntry;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateInvoiceStatus
{
    public function __construct(
        private AuditLogger $auditLogger,
        private PostInvoiceRevenue $postInvoiceRevenue,
        private VoidFinancialEntry $voidFinancialEntry,
    ) {}

    public function handle(Invoice $invoice, InvoiceStatus $status): Invoice
    {
        if ($invoice->status === InvoiceStatus::Void) {
            throw new InvalidArgumentException('Void invoices cannot be updated.');
        }

        return DB::transaction(function () use ($invoice, $status) {
            $old = $invoice->toArray();
            $updates = ['status' => $status];

            if ($status === InvoiceStatus::Paid) {
                $updates['paid_at'] = now();
            }

            $invoice->update($updates);
            $fresh = $invoice->fresh();

            if ($status === InvoiceStatus::Paid) {
                $this->postInvoiceRevenue->handle($fresh ?? $invoice);
            }

            if ($status === InvoiceStatus::Void) {
                FinancialEntry::query()
                    ->where('invoice_id', $invoice->id)
                    ->where('entry_type', FinancialEntryType::Revenue)
                    ->where('status', FinancialEntryStatus::Posted)
                    ->whereNull('void_of_id')
                    ->get()
                    ->each(fn (FinancialEntry $entry) => $this->voidFinancialEntry->handle($entry));
            }

            $this->auditLogger->log('invoice.status_updated', $invoice, $old, $fresh?->toArray());
            ActivityHelper::log('Invoice status updated', $invoice, ['status' => $status->value]);

            return $fresh ?? $invoice;
        });
    }
}
