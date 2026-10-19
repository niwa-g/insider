<?php

namespace Database\Factories\Finance;

use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Enums\DiscountType;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 1000);

        return [
            'client_id' => Client::factory(),
            'number' => 'INV-'.fake()->unique()->numerify('####'),
            'status' => InvoiceStatus::Draft,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => $subtotal,
            'discount_type' => DiscountType::Flat,
            'discount_value' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => $subtotal,
            'currency' => 'USD',
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Invoice $invoice): void {
            if (! $invoice->organization_id && $invoice->client_id) {
                $invoice->organization_id = Client::query()->find($invoice->client_id)?->organization_id;
            }
        });
    }
}
