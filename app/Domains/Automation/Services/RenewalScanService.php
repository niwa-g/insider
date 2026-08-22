<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Actions\RecordAutomationRun;
use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Models\AutomationSchedule;
use App\Domains\Finance\Enums\RenewalStatus;
use App\Domains\Finance\Models\Renewal;
use App\Domains\Identity\Models\Organization;
use App\Models\User;

class RenewalScanService
{
    public function __construct(
        private AutomationRuleEvaluator $evaluator,
        private RecordAutomationRun $recordRun,
    ) {}

    /** @return array{organizations: int, renewals: int, notified: int} */
    public function scanAll(?AutomationSchedule $schedule = null, ?User $triggeredBy = null): array
    {
        $orgs = Organization::query()->pluck('id');
        $totalRenewals = 0;
        $totalNotified = 0;

        foreach ($orgs as $orgId) {
            $result = $this->scanOrganization($orgId, $schedule, $triggeredBy);
            $totalRenewals += $result['renewals'];
            $totalNotified += $result['notified'];
        }

        return [
            'organizations' => $orgs->count(),
            'renewals' => $totalRenewals,
            'notified' => $totalNotified,
        ];
    }

    /** @return array{renewals: int, notified: int} */
    public function scanOrganization(
        string $organizationId,
        ?AutomationSchedule $schedule = null,
        ?User $triggeredBy = null,
    ): array {
        $config = $schedule?->config ?? ['renewal_days' => [7, 14, 30]];
        $days = collect($config['renewal_days'] ?? [14])->map(fn ($d) => (int) $d)->max() ?? 14;

        $renewals = Renewal::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->whereIn('status', [RenewalStatus::Upcoming, RenewalStatus::Due, RenewalStatus::Overdue])
            ->whereDate('renewal_date', '<=', now()->addDays($days)->toDateString())
            ->get();

        $run = $this->recordRun->start(
            $organizationId,
            AutomationTrigger::RenewalExpiring,
            schedule: $schedule,
            triggeredBy: $triggeredBy,
        );

        $notified = 0;
        $matched = 0;

        try {
            foreach ($renewals as $renewal) {
                $daysUntil = (int) now()->startOfDay()->diffInDays($renewal->renewal_date, false);
                $context = [
                    'renewal_id' => $renewal->id,
                    'title' => $renewal->title,
                    'days_until_renewal' => $daysUntil,
                    'renewal_date' => $renewal->renewal_date?->toDateString(),
                    'owner_id' => $renewal->owner_id,
                    'notifiable' => $renewal,
                    'body' => "Renewal \"{$renewal->title}\" is due in {$daysUntil} day(s).",
                ];

                $result = $this->evaluator->evaluate(
                    $organizationId,
                    AutomationTrigger::RenewalExpiring,
                    $context,
                );

                if ($result['matched'] > 0) {
                    $matched++;
                }
                $notified += $result['notified'];
            }

            $this->recordRun->finish($run, AutomationRunStatus::Succeeded, 'Renewal scan completed.', [
                'matched_count' => $matched,
                'notified_count' => $notified,
                'renewals_scanned' => $renewals->count(),
            ]);
        } catch (\Throwable $e) {
            $this->recordRun->finish($run, AutomationRunStatus::Failed, $e->getMessage());

            throw $e;
        }

        return ['renewals' => $renewals->count(), 'notified' => $notified];
    }
}
