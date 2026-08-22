<?php

namespace App\Http\Controllers;

use App\Domains\Assets\Enums\DeploymentStatus;
use App\Domains\Assets\Models\Deployment;
use App\Domains\Crm\Enums\ClientStatus;
use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Enums\ProjectHealthStatus;
use App\Domains\Delivery\Enums\ProjectStatus;
use App\Domains\Delivery\Enums\TaskStatus;
use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\Task;
use App\Domains\Finance\Enums\RenewalStatus;
use App\Domains\Finance\Models\Renewal;
use App\Domains\Finance\Services\MrrCalculator;
use App\Domains\Finance\Services\ProfitabilityCalculator;
use App\Domains\Operations\Enums\TicketStatus;
use App\Domains\Operations\Models\Ticket;
use App\Domains\Platform\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        MrrCalculator $mrrCalculator,
        ProfitabilityCalculator $profitabilityCalculator,
    ): Response {
        $user = $request->user();
        $orgId = $user?->organization_id;

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $monthly = $orgId
            ? $profitabilityCalculator->forOrganization($orgId, null, $monthStart, $monthEnd)
            : ['revenue' => 0.0, 'cost' => 0.0, 'profit' => 0.0];

        $openTicketStatuses = [
            TicketStatus::Open,
            TicketStatus::InProgress,
            TicketStatus::Waiting,
        ];

        $activeRenewalStatuses = [
            RenewalStatus::Upcoming,
            RenewalStatus::Due,
            RenewalStatus::Overdue,
        ];

        $kpis = [
            'clients' => $orgId ? Client::query()->count() : 0,
            'active_clients' => $orgId ? Client::query()->where('status', ClientStatus::Active)->count() : 0,
            'projects' => $orgId ? Project::query()->count() : 0,
            'active_projects' => $orgId ? Project::query()->where('status', ProjectStatus::Active)->count() : 0,
            'mrr' => $orgId ? round($mrrCalculator->forOrganization($orgId), 2) : 0,
            'monthly_revenue' => round($monthly['revenue'], 2),
            'monthly_costs' => round($monthly['cost'], 2),
            'estimated_profit' => round($monthly['profit'], 2),
            'open_tickets' => $orgId
                ? Ticket::query()->whereIn('status', $openTicketStatuses)->count()
                : 0,
            'failed_deployments' => $orgId
                ? Deployment::query()->where('status', DeploymentStatus::Failed)->count()
                : 0,
            'expiring_renewals' => $orgId
                ? Renewal::query()
                    ->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
                    ->whereIn('status', $activeRenewalStatuses)
                    ->count()
                : 0,
        ];

        $attention = [
            'renewals' => $this->renewalAttention($orgId),
            'overdue_tasks' => $this->overdueTaskAttention($orgId),
            'at_risk_projects' => $this->atRiskProjectAttention($orgId),
            'sla_breaches' => $this->slaBreachAttention($orgId),
        ];

        return Inertia::render('Dashboard/Index', [
            'kpis' => $kpis,
            'attention' => $attention,
            'recentAudit' => AuditLog::query()
                ->when($orgId, fn ($q) => $q->where('organization_id', $orgId))
                ->with('user:id,name')
                ->latest('created_at')
                ->limit(10)
                ->get(['id', 'action', 'created_at', 'user_id']),
            'userRole' => $user?->getRoleNames()->first(),
        ]);
    }

    /** @return list<array{id: string, title: string, href: string, meta: string}> */
    private function renewalAttention(?string $orgId): array
    {
        if (! $orgId) {
            return [];
        }

        return Renewal::query()
            ->with('client:id,company_name')
            ->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->whereIn('status', [RenewalStatus::Upcoming, RenewalStatus::Due, RenewalStatus::Overdue])
            ->orderBy('renewal_date')
            ->limit(10)
            ->get(['id', 'title', 'renewal_date', 'client_id'])
            ->map(fn (Renewal $renewal) => [
                'id' => $renewal->id,
                'title' => $renewal->title,
                'href' => '/renewals?bucket=30',
                'meta' => collect([
                    $renewal->renewal_date?->format('M j, Y'),
                    $renewal->client?->company_name,
                ])->filter()->implode(' · '),
            ])
            ->all();
    }

    /** @return list<array{id: string, title: string, href: string, meta: string}> */
    private function overdueTaskAttention(?string $orgId): array
    {
        if (! $orgId) {
            return [];
        }

        return Task::query()
            ->with('client:id,company_name')
            ->whereNotIn('status', [TaskStatus::Done, TaskStatus::Cancelled])
            ->whereDate('due_date', '<', now()->toDateString())
            ->orderBy('due_date')
            ->limit(10)
            ->get(['id', 'title', 'due_date', 'client_id'])
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'href' => $task->client_id ? "/tasks?client_id={$task->client_id}" : '/tasks',
                'meta' => collect([
                    $task->due_date?->format('M j, Y'),
                    $task->client?->company_name,
                ])->filter()->implode(' · '),
            ])
            ->all();
    }

    /** @return list<array{id: string, title: string, href: string, meta: string}> */
    private function atRiskProjectAttention(?string $orgId): array
    {
        if (! $orgId) {
            return [];
        }

        return Project::query()
            ->with('client:id,company_name')
            ->whereIn('health_status', [ProjectHealthStatus::AtRisk, ProjectHealthStatus::Critical])
            ->whereNotIn('status', [ProjectStatus::Completed, ProjectStatus::Cancelled])
            ->orderBy('health_score')
            ->limit(10)
            ->get(['id', 'name', 'health_status', 'health_score', 'client_id'])
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'title' => $project->name,
                'href' => "/projects/{$project->id}",
                'meta' => collect([
                    $project->health_status?->label(),
                    $project->health_score !== null ? "Score {$project->health_score}" : null,
                    $project->client?->company_name,
                ])->filter()->implode(' · '),
            ])
            ->all();
    }

    /** @return list<array{id: string, title: string, href: string, meta: string}> */
    private function slaBreachAttention(?string $orgId): array
    {
        if (! $orgId) {
            return [];
        }

        $openStatuses = [TicketStatus::Open, TicketStatus::InProgress, TicketStatus::Waiting];

        return Ticket::query()
            ->with('client:id,company_name')
            ->whereIn('status', $openStatuses)
            ->where(function ($q) {
                $q->where('sla_breached', true)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('first_responded_at')
                            ->whereNotNull('respond_by')
                            ->where('respond_by', '<', now());
                    })
                    ->orWhere(function ($q2) {
                        $q2->whereNull('resolved_at')
                            ->whereNotNull('resolve_by')
                            ->where('resolve_by', '<', now());
                    });
            })
            ->orderBy('respond_by')
            ->limit(10)
            ->get(['id', 'number', 'subject', 'client_id', 'sla_breached'])
            ->map(fn (Ticket $ticket) => [
                'id' => $ticket->id,
                'title' => "{$ticket->number}: {$ticket->subject}",
                'href' => "/tickets/{$ticket->id}",
                'meta' => $ticket->client?->company_name ?? 'SLA breach',
            ])
            ->all();
    }
}
