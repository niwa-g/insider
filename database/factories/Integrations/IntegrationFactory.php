<?php

namespace Database\Factories\Integrations;

use App\Domains\Identity\Models\Organization;
use App\Domains\Integrations\Enums\IntegrationCategory;
use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Models\Integration;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Integration> */
class IntegrationFactory extends Factory
{
    protected $model = Integration::class;

    public function definition(): array
    {
        $provider = fake()->randomElement(IntegrationProvider::cases());

        return [
            'organization_id' => Organization::factory(),
            'name' => $provider->label().' '.fake()->word(),
            'provider' => $provider,
            'category' => $provider->defaultCategory(),
            'status' => IntegrationStatus::Disconnected,
            'config' => ['region' => 'us-east-1'],
            'is_enabled' => true,
        ];
    }

    public function connected(): static
    {
        return $this->state(fn () => ['status' => IntegrationStatus::Connected]);
    }

    public function aws(): static
    {
        return $this->state(fn () => [
            'provider' => IntegrationProvider::Aws,
            'category' => IntegrationCategory::Cloud,
            'name' => 'AWS Production',
            'config' => ['region' => 'us-east-1', 'account_id' => '123456789012'],
        ]);
    }
}
