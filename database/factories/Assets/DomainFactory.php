<?php

namespace Database\Factories\Assets;

use App\Domains\Assets\Enums\DomainStatus;
use App\Domains\Assets\Models\Domain;
use App\Domains\Crm\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Domain> */
class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => fake()->unique()->domainName(),
            'registrar' => fake()->company(),
            'registered_at' => now()->subYear()->toDateString(),
            'expires_at' => now()->addYear()->toDateString(),
            'auto_renew' => true,
            'status' => DomainStatus::Active,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Domain $domain): void {
            if (! $domain->organization_id && $domain->client_id) {
                $domain->organization_id = Client::query()->find($domain->client_id)?->organization_id;
            }
        });
    }
}
