<?php

namespace App\Domains\Operations\Actions;

use App\Domains\Operations\Enums\TicketPriority;
use App\Domains\Operations\Models\SlaPolicy;
use App\Domains\Operations\Models\Ticket;
use App\Domains\Operations\Services\CreateNotification;
use App\Domains\Operations\Services\SlaCalculator;
use App\Domains\Automation\Events\TicketCreated;
use App\Domains\Platform\Services\AuditLogger;
use App\Models\User;
use App\Support\ActivityHelper;
use Illuminate\Support\Facades\DB;

class UpsertTicket
{
    public function __construct(
        private AuditLogger $auditLogger,
        private SlaCalculator $slaCalculator,
        private CreateNotification $createNotification,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(?Ticket $model, array $data, ?User $actor = null): Ticket
    {
        return DB::transaction(function () use ($model, $data, $actor) {
            $previousAssigneeId = $model?->assignee_id;

            if ($model === null) {
                $data['number'] = $this->nextNumber($data['organization_id']);
                $data = $this->applySlaDeadlines($data);
                $model = Ticket::query()->create($data);
                $this->auditLogger->log('ticket.created', $model, null, $model->toArray());
                ActivityHelper::log('Ticket created', $model);
                TicketCreated::dispatch($model);
            } else {
                $old = $model->toArray();
                if (isset($data['priority']) && $data['priority'] !== $model->getRawOriginal('priority')) {
                    $data = $this->applySlaDeadlines(array_merge($model->toArray(), $data));
                }
                $model->update($data);
                $this->auditLogger->log('ticket.updated', $model, $old, $model->fresh()->toArray());
                ActivityHelper::log('Ticket updated', $model);
            }

            $model = $this->slaCalculator->applyBreachFlag($model->fresh());

            if ($actor && $model->assignee_id && $model->assignee_id !== $previousAssigneeId) {
                $assignee = User::query()->find($model->assignee_id);
                if ($assignee) {
                    $this->createNotification->handle(
                        $assignee,
                        'ticket.assigned',
                        "Ticket {$model->number} assigned to you",
                        $model->subject,
                        ['ticket_id' => $model->id],
                        $model,
                    );
                }
            }

            return $model->fresh(['client', 'assignee', 'slaPolicy']);
        });
    }

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    private function applySlaDeadlines(array $data): array
    {
        $policy = null;
        if (! empty($data['sla_policy_id'])) {
            $policy = SlaPolicy::query()->with('targets')->find($data['sla_policy_id']);
        }
        if ($policy === null) {
            $policy = SlaPolicy::query()
                ->with('targets')
                ->where('organization_id', $data['organization_id'])
                ->where('is_default', true)
                ->first();
            if ($policy) {
                $data['sla_policy_id'] = $policy->id;
            }
        }

        if ($policy) {
            $priority = TicketPriority::from($data['priority'] ?? 'medium');
            $deadlines = $this->slaCalculator->computeDeadlines($policy, $priority);
            $data['respond_by'] = $deadlines['respond_by'];
            $data['resolve_by'] = $deadlines['resolve_by'];
        }

        return $data;
    }

    private function nextNumber(string $organizationId): string
    {
        $last = Ticket::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->orderByDesc('number')
            ->value('number');

        $seq = 1;
        if ($last && preg_match('/TCK-(\d+)/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('TCK-%04d', $seq);
    }
}
