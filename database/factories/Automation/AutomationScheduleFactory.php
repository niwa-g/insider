<?php

namespace Database\Factories\Automation;

use App\Domains\Automation\Enums\AutomationScheduleJobType;
use App\Domains\Automation\Enums\ScheduleFrequency;
use App\Domains\Automation\Models\AutomationSchedule;
use App\Domains\Identity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AutomationSchedule> */
class AutomationScheduleFactory extends Factory
{
    protected $model = AutomationSchedule::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true),
            'job_type' => AutomationScheduleJobType::ScanRenewals,
            'frequency' => ScheduleFrequency::Daily,
            'is_enabled' => true,
            'config' => ['renewal_days' => [7, 14, 30]],
        ];
    }
}
