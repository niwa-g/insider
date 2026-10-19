<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Finance\Enums\DiscountType;
use App\Domains\Finance\Models\Invoice;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CreateInvoiceFromClientServices
{
    public function __construct(private UpsertInvoice $upsertInvoice) {}

    /**
     * @param  list<string>  $clientServiceIds
     * @param  array{issue_date?: string, due_date?: ?string, notes?: ?string}  $options
     */
    public function handle(string $organizationId, string $clientId, array $clientServiceIds, array $options = []): Invoice
    {
        if ($clientServiceIds === []) {
            throw new InvalidArgumentException('Select at least one client service.');
        }

        /** @var Collection<int, ClientService> $services */
        $services = ClientService::query()
            ->where('client_id', $clientId)
            ->whereIn('id', $clientServiceIds)
            ->get();

        if ($services->count() !== count(array_unique($clientServiceIds))) {
            throw new InvalidArgumentException('One or more services are invalid for this client.');
        }

        $lines = $services->map(fn (ClientService $service) => [
            'description' => $service->name,
            'quantity' => 1,
            'unit_price' => $service->selling_price,
            'client_service_id' => $service->id,
        ])->all();

        return $this->upsertInvoice->handle(null, [
            'organization_id' => $organizationId,
            'client_id' => $clientId,
            'issue_date' => $options['issue_date'] ?? now()->toDateString(),
            'due_date' => $options['due_date'] ?? now()->addDays(14)->toDateString(),
            'discount_type' => DiscountType::Flat->value,
            'discount_value' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'notes' => $options['notes'] ?? null,
        ], $lines);
    }
}
