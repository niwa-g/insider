<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Models\Ticket;
use App\Domains\Operations\Models\TicketComment;
use App\Domains\Operations\Services\CreateNotification;
use App\Domains\Operations\Services\SlaCalculator;
use App\Domains\Platform\Services\AuditLogger;
use App\Models\User;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class AddTicketComment
{
    public function __construct(
        private AuditLogger $auditLogger,
        private SlaCalculator $slaCalculator,
        private CreateNotification $createNotification,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(Ticket $ticket, array $data, User $user): TicketComment
    {
        return DB::transaction(function () use ($ticket, $data, $user) {
            $data['ticket_id'] = $ticket->id;
            $data['organization_id'] = $ticket->organization_id;
            $data['user_id'] = $user->id;

            $comment = TicketComment::query()->create($data);

            if (! $data['is_internal'] && ! $ticket->first_responded_at) {
                $ticket->update(['first_responded_at' => now()]);
            }

            $this->auditLogger->log('ticket.comment_added', $ticket, null, $comment->toArray());
            ActivityHelper::log('Ticket comment added', $ticket);

            $this->slaCalculator->applyBreachFlag($ticket->fresh());

            if ($ticket->assignee_id && $ticket->assignee_id !== $user->id) {
                $assignee = User::query()->find($ticket->assignee_id);
                if ($assignee) {
                    $this->createNotification->handle(
                        $assignee,
                        'ticket.comment',
                        "New comment on {$ticket->number}",
                        \Illuminate\Support\Str::limit($data['body'], 120),
                        ['ticket_id' => $ticket->id],
                        $ticket,
                    );
                }
            }

            return $comment->fresh(['user']);
        });
    }
}
