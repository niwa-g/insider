<?php

namespace App\Domains\Automation\Actions;

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Automation\Services\AutomationRuleEvaluator;
use App\Models\User;

class RunAutomationRuleNow
{
    public function __construct(
        private AutomationRuleEvaluator $evaluator,
        private RecordAutomationRun $recordRun,
    ) {}

    /** @param  array<string, mixed>  $context */
    public function handle(AutomationRule $rule, ?User $triggeredBy = null, array $context = []): array
    {
        if (! $rule->is_enabled) {
            $run = $this->recordRun->start(
                $rule->organization_id,
                $rule->trigger,
                rule: $rule,
                triggeredBy: $triggeredBy,
            );
            $this->recordRun->finish($run, AutomationRunStatus::Skipped, 'Rule is disabled.');

            return ['status' => 'skipped', 'matched' => 0, 'notified' => 0];
        }

        $run = $this->recordRun->start(
            $rule->organization_id,
            $rule->trigger,
            rule: $rule,
            triggeredBy: $triggeredBy,
        );

        try {
            $result = $this->evaluator->evaluate(
                $rule->organization_id,
                $rule->trigger,
                $context,
                $rule,
            );

            $this->recordRun->finish($run, AutomationRunStatus::Succeeded, 'Rule executed.', [
                'matched_count' => $result['matched'],
                'notified_count' => $result['notified'],
            ]);

            return ['status' => 'succeeded', ...$result];
        } catch (\Throwable $e) {
            $this->recordRun->finish($run, AutomationRunStatus::Failed, $e->getMessage());

            throw $e;
        }
    }
}
