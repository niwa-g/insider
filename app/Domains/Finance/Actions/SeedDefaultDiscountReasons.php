<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\DiscountReasonStatus;
use App\Domains\Finance\Models\DiscountReason;

class SeedDefaultDiscountReasons
{
    /** @var list<array{name: string, code: string, description: string, sort_order: int}> */
    public const DEFAULTS = [
        [
            'name' => 'Loyalty discount',
            'code' => 'loyalty',
            'description' => 'Discount for long-term or high-value clients.',
            'sort_order' => 10,
        ],
        [
            'name' => 'Volume / bulk',
            'code' => 'volume',
            'description' => 'Discount for multi-unit or bulk purchasing.',
            'sort_order' => 20,
        ],
        [
            'name' => 'Promotional offer',
            'code' => 'promotional',
            'description' => 'Campaign, launch, or seasonal promotion.',
            'sort_order' => 30,
        ],
        [
            'name' => 'Early payment',
            'code' => 'early_payment',
            'description' => 'Incentive for paying ahead of due date.',
            'sort_order' => 40,
        ],
        [
            'name' => 'Goodwill / service credit',
            'code' => 'goodwill',
            'description' => 'Credit for service issues or relationship goodwill.',
            'sort_order' => 50,
        ],
        [
            'name' => 'Contract negotiation',
            'code' => 'negotiation',
            'description' => 'Agreed commercial concession during contracting.',
            'sort_order' => 60,
        ],
        [
            'name' => 'Billing correction',
            'code' => 'correction',
            'description' => 'Adjustment to correct a prior billing error.',
            'sort_order' => 70,
        ],
        [
            'name' => 'Other',
            'code' => 'other',
            'description' => 'Custom reason not covered by standard types.',
            'sort_order' => 100,
        ],
    ];

    public function handle(string $organizationId): void
    {
        foreach (self::DEFAULTS as $reason) {
            DiscountReason::withoutGlobalScopes()->firstOrCreate(
                [
                    'organization_id' => $organizationId,
                    'code' => $reason['code'],
                ],
                [
                    'name' => $reason['name'],
                    'description' => $reason['description'],
                    'is_system' => true,
                    'status' => DiscountReasonStatus::Active,
                    'sort_order' => $reason['sort_order'],
                ],
            );
        }
    }
}
