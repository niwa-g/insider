<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Models\AutomationRule;
use App\Domains\Operations\Services\CreateNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AutomationRuleEvaluator
{
    public function __construct(
        private CreateNotification $createNotification,
    ) {}

    /** @param  array<string, mixed>  $context  @return array{matched: int, notified: int, skipped: int} */
    public function evaluate(
        string $organizationId,
        AutomationTrigger $trigger,
        array $context = [],
        ?AutomationRule $singleRule = null,
    ): array {
        $rules = $singleRule
            ? collect([$singleRule])
            : AutomationRule::withoutGlobalScopes()
                ->where('organization_id', $organizationId)
                ->where('trigger', $trigger)
                ->where('is_enabled', true)
                ->get();

        $matched = 0;
        $notified = 0;
        $skipped = 0;

        foreach ($rules as $rule) {
            if (! $this->conditionsMatch($rule, $context)) {
                $skipped++;

                continue;
            }

            $matched++;
            $notified += $this->executeActions($rule, $context);
        }

        return ['matched' => $matched, 'notified' => $notified, 'skipped' => $skipped];
    }

    /** @param  array<string, mixed>  $context */
    private function conditionsMatch(AutomationRule $rule, array $context): bool
    {
        $conditions = $rule->conditions ?? [];

        if (isset($conditions['days_ahead']) && isset($context['days_until_renewal'])) {
            $daysAhead = (int) $conditions['days_ahead'];
            $daysUntil = (int) $context['days_until_renewal'];

            if ($daysUntil < 0 || $daysUntil > $daysAhead) {
                return false;
            }
        }

        if (isset($conditions['priorities']) && is_array($conditions['priorities'])) {
            $priority = $context['priority'] ?? null;
            if ($priority === null || ! in_array($priority, $conditions['priorities'], true)) {
                return false;
            }
        }

        return true;
    }

    /** @param  array<string, mixed>  $context */
    private function executeActions(AutomationRule $rule, array $context): int
    {
        $users = $this->resolveRecipients($rule, $context);
        $notifiable = $context['notifiable'] ?? null;
        $title = $this->buildTitle($rule, $context);
        $body = $this->buildBody($rule, $context);
        $count = 0;

        foreach ($users->unique('id') as $user) {
            if ($rule->notify_in_app) {
                $this->createNotification->handle(
                    $user,
                    'automation.'.$rule->trigger->value,
                    $title,
                    $body,
                    ['rule_id' => $rule->id, 'context' => $context],
                    $notifiable instanceof Model ? $notifiable : null,
                );
                $count++;
            }

            if ($rule->notify_email) {
                Log::info('Automation email stub', [
                    'user_id' => $user->id,
                    'rule_id' => $rule->id,
                    'title' => $title,
                ]);
            }
        }

        return $count;
    }

    /** @param  array<string, mixed>  $context  @return Collection<int, User> */
    private function resolveRecipients(AutomationRule $rule, array $context): Collection
    {
        $users = collect();

        foreach ($rule->actions as $action) {
            $type = $action['type'] ?? null;

            match ($type) {
                'notify_users' => $users = $users->merge(
                    $this->usersByRole($rule->organization_id, $action['role'] ?? 'Administrator')
                ),
                'notify_assignee' => $users = $users->merge(
                    $this->assigneeFromContext($context)
                ),
                'notify_user_ids' => $users = $users->merge(
                    $this->usersByIds($rule->organization_id, $action['user_ids'] ?? [])
                ),
                'notify_owner' => $users = $users->merge(
                    $this->ownerFromContext($context)
                ),
                default => null,
            };
        }

        return $users->filter();
    }

    /** @return Collection<int, User> */
    private function usersByRole(string $organizationId, string $roleName): Collection
    {
        $role = Role::findByName($roleName, 'web');
        if (! $role) {
            return collect();
        }

        return User::query()
            ->where('organization_id', $organizationId)
            ->role($role)
            ->get();
    }

    /** @param  array<int, string|int>  $userIds  @return Collection<int, User> */
    private function usersByIds(string $organizationId, array $userIds): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        return User::query()
            ->where('organization_id', $organizationId)
            ->whereIn('id', $userIds)
            ->get();
    }

    /** @param  array<string, mixed>  $context  @return Collection<int, User> */
    private function assigneeFromContext(array $context): Collection
    {
        $assignee = $context['assignee'] ?? null;
        if ($assignee instanceof User) {
            return collect([$assignee]);
        }

        if (! empty($context['assignee_id'])) {
            $user = User::query()->find($context['assignee_id']);

            return $user ? collect([$user]) : collect();
        }

        return collect();
    }

    /** @param  array<string, mixed>  $context  @return Collection<int, User> */
    private function ownerFromContext(array $context): Collection
    {
        $owner = $context['owner'] ?? null;
        if ($owner instanceof User) {
            return collect([$owner]);
        }

        if (! empty($context['owner_id'])) {
            $user = User::query()->find($context['owner_id']);

            return $user ? collect([$user]) : collect();
        }

        return collect();
    }

    /** @param  array<string, mixed>  $context */
    private function buildTitle(AutomationRule $rule, array $context): string
    {
        return $context['title'] ?? $rule->name;
    }

    /** @param  array<string, mixed>  $context */
    private function buildBody(AutomationRule $rule, array $context): ?string
    {
        return $context['body'] ?? $rule->description;
    }
}
