<?php

namespace App\Http\Controllers\Operations;

use App\Domains\Operations\Actions\DeleteSlaPolicy;
use App\Domains\Operations\Actions\UpsertSlaPolicy;
use App\Domains\Operations\Enums\SlaPolicyStatus;
use App\Domains\Operations\Enums\TicketPriority;
use App\Domains\Operations\Models\SlaPolicy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\UpsertSlaPolicyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SlaPolicyController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SlaPolicy::class);

        $policies = SlaPolicy::query()
            ->withCount('targets')
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Operations/SlaPolicies/Index', [
            'policies' => $policies,
            'filters' => ['search' => $request->string('search')->toString()],
            'statuses' => SlaPolicyStatus::options(),
            'can' => [
                'create' => $request->user()?->can('sla.create') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SlaPolicy::class);

        return Inertia::render('Operations/SlaPolicies/Form', [
            'policy' => null,
            'targets' => $this->defaultTargets(),
            'statuses' => SlaPolicyStatus::options(),
            'priorities' => TicketPriority::options(),
        ]);
    }

    public function store(UpsertSlaPolicyRequest $request, UpsertSlaPolicy $action): RedirectResponse
    {
        $data = $request->validated();
        $targets = $data['targets'] ?? [];
        unset($data['targets']);
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data, $targets);

        return redirect()->route('sla-policies.index')->with('success', 'SLA policy created.');
    }

    public function edit(SlaPolicy $sla_policy): Response
    {
        $this->authorize('update', $sla_policy);

        $sla_policy->load('targets');

        return Inertia::render('Operations/SlaPolicies/Form', [
            'policy' => [
                ...$sla_policy->toArray(),
                'status' => $sla_policy->getRawOriginal('status'),
            ],
            'targets' => $sla_policy->targets->map(fn ($t) => [
                'priority' => $t->getRawOriginal('priority'),
                'respond_within_minutes' => $t->respond_within_minutes,
                'resolve_within_minutes' => $t->resolve_within_minutes,
            ])->values()->all() ?: $this->defaultTargets(),
            'statuses' => SlaPolicyStatus::options(),
            'priorities' => TicketPriority::options(),
        ]);
    }

    public function update(UpsertSlaPolicyRequest $request, SlaPolicy $sla_policy, UpsertSlaPolicy $action): RedirectResponse
    {
        $data = $request->validated();
        $targets = $data['targets'] ?? [];
        unset($data['targets']);
        $action->handle($sla_policy, $data, $targets);

        return redirect()->route('sla-policies.index')->with('success', 'SLA policy updated.');
    }

    public function destroy(SlaPolicy $sla_policy, DeleteSlaPolicy $action): RedirectResponse
    {
        $this->authorize('delete', $sla_policy);
        $action->handle($sla_policy);

        return redirect()->route('sla-policies.index')->with('success', 'SLA policy deleted.');
    }

    /** @return array<int, array<string, mixed>> */
    private function defaultTargets(): array
    {
        return [
            ['priority' => 'low', 'respond_within_minutes' => 480, 'resolve_within_minutes' => 2880],
            ['priority' => 'medium', 'respond_within_minutes' => 240, 'resolve_within_minutes' => 1440],
            ['priority' => 'high', 'respond_within_minutes' => 60, 'resolve_within_minutes' => 480],
            ['priority' => 'urgent', 'respond_within_minutes' => 15, 'resolve_within_minutes' => 240],
        ];
    }
}
