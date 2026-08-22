<?php

namespace App\Domains\Automation\Listeners;

use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Events\TicketCreated;
use App\Domains\Automation\Services\AutomationRuleEvaluator;

class EvaluateTicketCreatedRules
{
    public function __construct(private AutomationRuleEvaluator $evaluator) {}

    public function handle(TicketCreated $event): void
    {
        $ticket = $event->ticket;

        $this->evaluator->evaluate(
            $ticket->organization_id,
            AutomationTrigger::TicketCreated,
            [
                'ticket_id' => $ticket->id,
                'title' => "New ticket: {$ticket->number}",
                'priority' => $ticket->getRawOriginal('priority'),
                'assignee_id' => $ticket->assignee_id,
                'notifiable' => $ticket,
                'body' => $ticket->subject,
            ],
        );
    }
}
