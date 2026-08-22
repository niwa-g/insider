<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Actions\RecordAutomationRun;
use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Models\AutomationSchedule;
use App\Domains\Identity\Models\Organization;
use App\Domains\Operations\Enums\TicketStatus;
use App\Domains\Operations\Models\Ticket;
use App\Domains\Operations\Services\SlaCalculator;
use App\Models\User;

class SlaScanService
{
    public function __construct(
        private AutomationRuleEvaluator $evaluator,
        private RecordAutomationRun $recordRun,
        private SlaCalculator $slaCalculator,
    ) {}

    /** @return array{organizations: int, breaches: int, notified: int} */
    public function scanAll(?AutomationSchedule $schedule = null, ?User $triggeredBy = null): array
    {
        $orgs = Organization::query()->pluck('id');
        $totalBreaches = 0;
        $totalNotified = 0;

        foreach ($orgs as $orgId) {
            $result = $this->scanOrganization($orgId, $schedule, $triggeredBy);
            $totalBreaches += $result['breaches'];
            $totalNotified += $result['notified'];
        }

        return [
            'organizations' => $orgs->count(),
            'breaches' => $totalBreaches,
            'notified' => $totalNotified,
        ];
    }

    /** @return array{breaches: int, notified: int} */
    public function scanOrganization(
        string $organizationId,
        ?AutomationSchedule $schedule = null,
        ?User $triggeredBy = null,
    ): array {
        $openStatuses = [TicketStatus::Open, TicketStatus::InProgress, TicketStatus::Waiting];

        $tickets = Ticket::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->whereIn('status', $openStatuses)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('first_responded_at')
                        ->whereNotNull('respond_by')
                        ->where('respond_by', '<', now());
                })->orWhere(function ($q2) {
                    $q2->whereNull('resolved_at')
                        ->whereNotNull('resolve_by')
                        ->where('resolve_by', '<', now());
                });
            })
            ->get();

        $run = $this->recordRun->start(
            $organizationId,
            AutomationTrigger::SlaBreach,
            schedule: $schedule,
            triggeredBy: $triggeredBy,
        );

        $notified = 0;
        $breaches = 0;

        try {
            foreach ($tickets as $ticket) {
                $this->slaCalculator->applyBreachFlag($ticket);
                $ticket = $ticket->fresh();

                if (! $ticket->sla_breached) {
                    continue;
                }

                $breaches++;
                $context = [
                    'ticket_id' => $ticket->id,
                    'title' => "SLA breached: {$ticket->number}",
                    'priority' => $ticket->getRawOriginal('priority'),
                    'assignee_id' => $ticket->assignee_id,
                    'notifiable' => $ticket,
                    'body' => $ticket->subject,
                ];

                $result = $this->evaluator->evaluate(
                    $organizationId,
                    AutomationTrigger::SlaBreach,
                    $context,
                );
                $notified += $result['notified'];
            }

            $this->recordRun->finish($run, AutomationRunStatus::Succeeded, 'SLA scan completed.', [
                'matched_count' => $breaches,
                'notified_count' => $notified,
            ]);
        } catch (\Throwable $e) {
            $this->recordRun->finish($run, AutomationRunStatus::Failed, $e->getMessage());

            throw $e;
        }

        return ['breaches' => $breaches, 'notified' => $notified];
    }
}
