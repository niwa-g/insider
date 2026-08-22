<?php

namespace Database\Factories\Operations;

use App\Domains\Operations\Enums\KnowledgeArticleStatus;
use App\Domains\Operations\Models\KnowledgeArticle;
use App\Domains\Operations\Models\KnowledgeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<KnowledgeArticle> */
class KnowledgeArticleFactory extends Factory
{
    protected $model = KnowledgeArticle::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'knowledge_category_id' => KnowledgeCategory::factory(),
            'title' => $title,
            'slug' => Str::slug(Str::limit($title, 60, '')),
            'summary' => fake()->optional()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'status' => KnowledgeArticleStatus::Draft,
            'is_internal' => true,
        ];
    }
}
