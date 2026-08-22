<?php

namespace Database\Factories\Operations;

use App\Domains\Crm\Models\Client;
use App\Domains\Operations\Enums\DocumentStatus;
use App\Domains\Operations\Enums\DocumentVisibility;
use App\Domains\Operations\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Document> */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $name = fake()->word().'.pdf';

        return [
            'client_id' => Client::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'disk' => 'local',
            'path' => 'documents/seed/'.$name,
            'original_name' => $name,
            'mime_type' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(1000, 500000),
            'visibility' => DocumentVisibility::Internal,
            'status' => DocumentStatus::Active,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Document $document): void {
            if (! $document->organization_id && $document->client_id) {
                $document->organization_id = Client::query()->find($document->client_id)?->organization_id;
            }
        });
    }
}
