<?php

namespace Database\Factories\Crm;

use App\Domains\Crm\Enums\ClientStatus;
use App\Domains\Crm\Models\Client;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $company = fake()->company();

        return [
            'organization_id' => Organization::factory(),
            'company_name' => $company,
            'trading_name' => $company,
            'client_code' => strtoupper(Str::random(6)),
            'client_type' => fake()->randomElement(['enterprise', 'smb', 'startup']),
            'industry' => fake()->randomElement(['Logistics', 'Retail', 'Healthcare', 'Finance']),
            'website' => fake()->url(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'country' => fake()->country(),
            'status' => ClientStatus::Active,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
