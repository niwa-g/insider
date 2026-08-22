<?php

namespace Database\Factories\Automation;

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AutomationRule> */
class AutomationRuleFactory extends Factory
{
    protected $model = AutomationRule::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'trigger' => AutomationTrigger::RenewalExpiring,
            'conditions' => ['days_ahead' => 14],
            'actions' => [
                ['type' => 'notify_users', 'role' => 'Administrator'],
            ],
            'notify_in_app' => true,
            'notify_email' => false,
            'is_enabled' => true,
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn () => ['is_enabled' => false]);
    }

    public function slaBreach(): static
    {
        return $this->state(fn () => [
            'trigger' => AutomationTrigger::SlaBreach,
            'conditions' => null,
            'actions' => [
                ['type' => 'notify_assignee'],
                ['type' => 'notify_users', 'role' => 'Administrator'],
            ],
        ]);
    }
}
