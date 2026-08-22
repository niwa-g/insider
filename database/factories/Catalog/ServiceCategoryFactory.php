<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Enums\ServiceStatus;
use App\Domains\Catalog\Models\ServiceCategory;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'organization_id' => Organization::factory(),
            'name' => ucfirst($name),
            'code' => strtoupper(Str::slug($name, '_')),
            'description' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(0, 20),
            'status' => ServiceStatus::Active,
        ];
    }
}
