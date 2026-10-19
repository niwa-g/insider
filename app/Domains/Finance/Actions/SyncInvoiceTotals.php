<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\DiscountType;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\InvoiceLine;

class SyncInvoiceTotals
{
    public function handle(
        Invoice $invoice,
        ?float $taxAmount = null,
        ?float $discountValue = null,
        ?DiscountType $discountType = null,
    ): Invoice {
        $subtotal = (float) $invoice->lines()->sum('line_total');
        $tax = $taxAmount ?? (float) $invoice->tax_amount;
        $type = $discountType ?? $invoice->discount_type ?? DiscountType::Flat;
        $value = $discountValue ?? (float) $invoice->discount_value;
        $discount = self::resolveDiscountAmount($subtotal, $type, $value);

        $invoice->update([
            'subtotal' => $subtotal,
            'discount_type' => $type,
            'discount_value' => max(0, $value),
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total' => round(max(0, $subtotal - $discount) + $tax, 2),
        ]);

        return $invoice->fresh(['lines', 'client:id,company_name']);
    }

    public static function resolveDiscountAmount(float $subtotal, DiscountType|string $type, float $value): float
    {
        $type = $type instanceof DiscountType ? $type : DiscountType::from($type);
        $value = max(0, $value);

        $discount = match ($type) {
            DiscountType::Percentage => round($subtotal * (min($value, 100) / 100), 2),
            DiscountType::Flat => $value,
        };

        return max(0, min($discount, $subtotal));
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

        return $this->handle(
            $invoice,
            (float) $invoice->tax_amount,
            (float) $invoice->discount_value,
            $invoice->discount_type,
        );
    }
}
