<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Enums\BillingCycle;
use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Enums\ClientServiceStatus;
use App\Domains\Catalog\Models\ClientService;
use App\Domains\Catalog\Models\ServiceType;
use App\Domains\Crm\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientService>
 */
class ClientServiceFactory extends Factory
{
    protected $model = ClientService::class;

    public function definition(): array
    {
        $cost = fake()->randomFloat(2, 20, 150);
        $price = $cost + fake()->randomFloat(2, 30, 200);

        return [
            'client_id' => Client::factory(),
            'service_type_id' => ServiceType::factory(),
            'name' => fake()->words(3, true).' Service',
            'start_date' => now()->subMonth()->toDateString(),
            'renewal_date' => now()->addDays(fake()->numberBetween(7, 90))->toDateString(),
            'billing_type' => BillingType::Recurring,
            'billing_cycle' => BillingCycle::Monthly,
            'cost' => $cost,
            'selling_price' => $price,
            'recurring_revenue' => $price,
            'status' => ClientServiceStatus::Active,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ClientService $service): void {
            if (! $service->organization_id && $service->client_id) {
                $service->organization_id = Client::query()->find($service->client_id)?->organization_id;
            }
        });
    }
}
