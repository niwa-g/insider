<?php

namespace Database\Factories\Integrations;

use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Models\IntegrationExternalRef;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IntegrationExternalRef> */
class IntegrationExternalRefFactory extends Factory
{
    protected $model = IntegrationExternalRef::class;

    public function definition(): array
    {
        return [
            'organization_id' => fn (array $attrs) => Integration::query()->find($attrs['integration_id'])?->organization_id,
            'integration_id' => Integration::factory(),
            'external_type' => 'ec2_instance',
            'external_id' => 'i-'.fake()->uuid(),
            'external_name' => fake()->domainWord().'-server',
            'payload' => ['region' => 'us-east-1'],
            'last_seen_at' => now(),
        ];
    }
}
