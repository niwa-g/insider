<?php

namespace Database\Factories\Automation;

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Models\AutomationRun;
use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AutomationRun> */
class AutomationRunFactory extends Factory
{
    protected $model = AutomationRun::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'automation_rule_id' => AutomationRule::factory(),
            'trigger' => AutomationTrigger::RenewalExpiring,
            'status' => AutomationRunStatus::Succeeded,
            'message' => 'Completed successfully.',
            'meta' => ['matched_count' => 1, 'notified_count' => 1],
            'started_at' => now()->subMinutes(5),
            'finished_at' => now()->subMinutes(4),
        ];
    }
}
