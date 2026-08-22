<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeleteInvoice
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Invoice $invoice): void
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            throw new InvalidArgumentException('Only draft invoices can be deleted.');
        }

        DB::transaction(function () use ($invoice) {
            $snapshot = $invoice->toArray();
            $invoice->lines()->delete();
            $invoice->delete();
            $this->auditLogger->log('invoice.deleted', $invoice, $snapshot, null);
            ActivityHelper::log('Invoice deleted', $invoice);
        });
    }
}
