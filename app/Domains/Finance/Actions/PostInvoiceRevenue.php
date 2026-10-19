<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\FinancialEntryCategory;
use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Enums\FinancialEntryType;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Models\FinancialEntry;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Platform\Services\AuditLogger;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostInvoiceRevenue
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Invoice $invoice): ?FinancialEntry
    {
        if ($invoice->status !== InvoiceStatus::Paid) {
            return null;
        }

        $existing = FinancialEntry::query()
            ->where('invoice_id', $invoice->id)
            ->where('entry_type', FinancialEntryType::Revenue)
            ->where('status', FinancialEntryStatus::Posted)
            ->whereNull('void_of_id')
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($invoice) {
            $entry = FinancialEntry::query()->create([
                'organization_id' => $invoice->organization_id,
                'client_id' => $invoice->client_id,
                'entry_type' => FinancialEntryType::Revenue,
                'category' => FinancialEntryCategory::Subscription,
                'description' => "Invoice {$invoice->number}",
                'amount' => $invoice->total,
                'currency' => $invoice->currency ?? 'USD',
                'occurred_on' => ($invoice->paid_at ?? now())->toDateString(),
                'status' => FinancialEntryStatus::Posted,
                'invoice_id' => $invoice->id,
                'created_by' => Auth::id(),
                'notes' => 'Auto-posted when invoice was fully paid.',
            ]);

            $this->auditLogger->log('financial_entry.created', $entry, null, $entry->toArray());
            ActivityHelper::log('Revenue posted from invoice', $entry, ['invoice_id' => $invoice->id]);

            return $entry;
        });
    }
}
