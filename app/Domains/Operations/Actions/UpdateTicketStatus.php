<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Enums\TicketStatus;
use App\Domains\Operations\Models\Ticket;
use App\Domains\Operations\Services\CreateNotification;
use App\Domains\Operations\Services\SlaCalculator;
use App\Domains\Platform\Services\AuditLogger;
use App\Models\User;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpdateTicketStatus
{
    public function __construct(
        private AuditLogger $auditLogger,
        private SlaCalculator $slaCalculator,
        private CreateNotification $createNotification,
    ) {}

    public function handle(Ticket $ticket, TicketStatus|string $status, ?User $actor = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $status, $actor) {
            $old = $ticket->toArray();
            $statusEnum = $status instanceof TicketStatus ? $status : TicketStatus::from($status);

            $payload = ['status' => $statusEnum];

            if ($statusEnum === TicketStatus::Resolved) {
                $payload['resolved_at'] = $ticket->resolved_at ?? now();
            } elseif ($statusEnum === TicketStatus::Closed) {
                $payload['closed_at'] = $ticket->closed_at ?? now();
                $payload['resolved_at'] = $ticket->resolved_at ?? now();
            }

            $ticket->update($payload);
            $this->auditLogger->log('ticket.status_updated', $ticket, $old, $ticket->fresh()->toArray());
            ActivityHelper::log('Ticket status updated', $ticket, ['status' => $statusEnum->value]);

            $ticket = $this->slaCalculator->applyBreachFlag($ticket->fresh());

            if ($actor && $ticket->assignee_id && $ticket->assignee_id !== $actor->id) {
                $assignee = User::query()->find($ticket->assignee_id);
                if ($assignee) {
                    $this->createNotification->handle(
                        $assignee,
                        'ticket.status_changed',
                        "Ticket {$ticket->number} status changed to {$statusEnum->label()}",
                        $ticket->subject,
                        ['ticket_id' => $ticket->id, 'status' => $statusEnum->value],
                        $ticket,
                    );
                }
            }

            return $ticket;
        });
    }
}
