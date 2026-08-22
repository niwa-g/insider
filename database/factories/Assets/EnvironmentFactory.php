<?php

namespace Database\Factories\Assets;

use App\Domains\Assets\Enums\EnvironmentSlug;
use App\Domains\Assets\Enums\EnvironmentStatus;
use App\Domains\Assets\Models\Application;
use App\Domains\Assets\Models\Environment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Environment> */
class EnvironmentFactory extends Factory
{
    protected $model = Environment::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'name' => 'Production',
            'slug' => EnvironmentSlug::Production,
            'url' => fake()->url(),
            'status' => EnvironmentStatus::Active,
            'branch' => 'main',
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Environment $environment): void {
            if (! $environment->organization_id && $environment->application_id) {
                $environment->organization_id = Application::query()->find($environment->application_id)?->organization_id;
            }
        });
    }
}
