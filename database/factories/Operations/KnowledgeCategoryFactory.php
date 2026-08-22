<?php

namespace Database\Factories\Operations;

use App\Domains\Operations\Enums\KnowledgeCategoryStatus;
use App\Domains\Operations\Models\KnowledgeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<KnowledgeCategory> */
class KnowledgeCategoryFactory extends Factory
{
    protected $model = KnowledgeCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(0, 10),
            'status' => KnowledgeCategoryStatus::Active,
        ];
    }
}
