<?php

namespace Database\Factories\Operations;

use App\Domains\Operations\Enums\SlaPolicyStatus;
use App\Domains\Operations\Models\SlaPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SlaPolicy> */
class SlaPolicyFactory extends Factory
{
    protected $model = SlaPolicy::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true).' SLA',
            'code' => strtoupper(fake()->lexify('SLA-???')),
            'description' => fake()->optional()->sentence(),
            'is_default' => false,
            'status' => SlaPolicyStatus::Active,
        ];
    }
}
