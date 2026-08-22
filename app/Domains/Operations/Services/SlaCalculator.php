<?php

namespace App\Domains\Operations\Services;

use App\Domains\Operations\Enums\TicketPriority;
use App\Domains\Operations\Enums\TicketStatus;
use App\Domains\Operations\Models\SlaPolicy;
use App\Domains\Operations\Models\SlaTarget;
use App\Domains\Operations\Models\Ticket;
use Carbon\Carbon;

class SlaCalculator
{
    /** @return array{respond_by: ?Carbon, resolve_by: ?Carbon} */
    public function computeDeadlines(SlaPolicy $policy, TicketPriority $priority): array
    {
        $target = $policy->targets
            ->first(fn (SlaTarget $t) => $t->priority === $priority);

        if ($target === null) {
            return ['respond_by' => null, 'resolve_by' => null];
        }

        $now = now();

        return [
            'respond_by' => $now->copy()->addMinutes($target->respond_within_minutes),
            'resolve_by' => $now->copy()->addMinutes($target->resolve_within_minutes),
        ];
    }

    public function isBreached(Ticket $ticket): bool
    {
        if (in_array($ticket->status, [TicketStatus::Resolved, TicketStatus::Closed], true)) {
            return (bool) $ticket->sla_breached;
        }

        $now = now();

        if ($ticket->respond_by && ! $ticket->first_responded_at && $now->gt($ticket->respond_by)) {
            return true;
        }

        if ($ticket->resolve_by && ! $ticket->resolved_at && $now->gt($ticket->resolve_by)) {
            return true;
        }

        return false;
    }

    public function applyBreachFlag(Ticket $ticket): Ticket
    {
        $ticket->update(['sla_breached' => $this->isBreached($ticket)]);

        return $ticket->fresh();
    }
}
