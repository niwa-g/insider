<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\InvoiceLine;

class SyncInvoiceTotals
{
    public function handle(Invoice $invoice, float $taxAmount = 0): Invoice
    {
        $subtotal = (float) $invoice->lines()->sum('line_total');
        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
        ]);

        return $invoice->fresh(['lines', 'client:id,company_name']);
    }

    /** @param  array<int, array<string, mixed>>  $lines */
    public static function computeLineTotal(array $line): float
    {
        $qty = (float) ($line['quantity'] ?? 1);
        $price = (float) ($line['unit_price'] ?? 0);

        return round($qty * $price, 2);
    }

    /** @param  array<int, array<string, mixed>>  $lines */
    public function syncLines(Invoice $invoice, array $lines): Invoice
    {
        $invoice->lines()->delete();

        foreach ($lines as $index => $line) {
            InvoiceLine::query()->create([
                'invoice_id' => $invoice->id,
                'description' => $line['description'],
                'quantity' => $line['quantity'] ?? 1,
                'unit_price' => $line['unit_price'],
                'line_total' => self::computeLineTotal($line),
                'client_service_id' => $line['client_service_id'] ?? null,
                'financial_entry_id' => $line['financial_entry_id'] ?? null,
                'sort_order' => $index,
            ]);
        }

        return $this->handle($invoice, (float) $invoice->tax_amount);
    }
}
