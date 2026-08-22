<?php

namespace Database\Factories\Delivery;

use App\Domains\Delivery\Enums\ProjectStatus;
use App\Domains\Delivery\Models\ProjectTemplate;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProjectTemplate>
 */
class ProjectTemplateFactory extends Factory
{
    protected $model = ProjectTemplate::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'organization_id' => Organization::factory(),
            'name' => ucfirst($name),
            'code' => strtoupper(Str::slug($name, '_')),
            'description' => fake()->optional()->sentence(),
            'default_status' => ProjectStatus::Planned,
            'is_active' => true,
        ];
    }
}
