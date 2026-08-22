<?php

namespace Database\Factories\Assets;

use App\Domains\Assets\Enums\HostingAccountStatus;
use App\Domains\Assets\Models\HostingAccount;
use App\Domains\Crm\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HostingAccount> */
class HostingAccountFactory extends Factory
{
    protected $model = HostingAccount::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => fake()->company().' Hosting',
            'plan' => 'Business',
            'primary_domain' => fake()->domainName(),
            'username' => fake()->userName(),
            'expires_at' => now()->addMonths(6)->toDateString(),
            'cost' => fake()->randomFloat(2, 20, 200),
            'status' => HostingAccountStatus::Active,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (HostingAccount $account): void {
            if (! $account->organization_id && $account->client_id) {
                $account->organization_id = Client::query()->find($account->client_id)?->organization_id;
            }
        });
    }
}
