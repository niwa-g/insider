<?php

namespace Database\Factories\Finance;

use App\Domains\Crm\Models\Client;
use App\Domains\Finance\Enums\FinancialEntryCategory;
use App\Domains\Finance\Enums\FinancialEntryStatus;
use App\Domains\Finance\Enums\FinancialEntryType;
use App\Domains\Finance\Models\FinancialEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialEntry>
 */
class FinancialEntryFactory extends Factory
{
    protected $model = FinancialEntry::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'entry_type' => FinancialEntryType::Revenue,
            'category' => FinancialEntryCategory::Hosting,
            'description' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 50, 500),
            'currency' => 'USD',
            'occurred_on' => now()->toDateString(),
            'status' => FinancialEntryStatus::Draft,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (FinancialEntry $entry): void {
            if (! $entry->organization_id && $entry->client_id) {
                $entry->organization_id = Client::query()->find($entry->client_id)?->organization_id;
            }
        });
    }

    public function posted(): static
    {
        return $this->state(['status' => FinancialEntryStatus::Posted]);
    }

    public function cost(): static
    {
        return $this->state(['entry_type' => FinancialEntryType::Cost]);
    }
}
