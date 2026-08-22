<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Enums\BillingCycle;
use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Enums\ServiceStatus;
use App\Domains\Catalog\Models\ServiceCategory;
use App\Domains\Catalog\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    protected $model = ServiceType::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'service_category_id' => ServiceCategory::factory(),
            'name' => ucfirst($name),
            'code' => strtoupper(Str::slug($name, '_')),
            'description' => fake()->optional()->sentence(),
            'billing_type' => BillingType::Recurring,
            'billing_cycle' => BillingCycle::Monthly,
            'default_cost' => fake()->randomFloat(2, 10, 200),
            'default_selling_price' => fake()->randomFloat(2, 50, 500),
            'status' => ServiceStatus::Active,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ServiceType $type): void {
            if (! $type->organization_id && $type->service_category_id) {
                $type->organization_id = ServiceCategory::query()
                    ->find($type->service_category_id)?->organization_id;
            }
        });
    }
}
