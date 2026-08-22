<?php

namespace App\Http\Controllers\Automation;

use App\Domains\Automation\Actions\DeleteAutomationRule;
use App\Domains\Automation\Actions\RunAutomationRuleNow;
use App\Domains\Automation\Actions\ToggleAutomationRule;
use App\Domains\Automation\Actions\UpsertAutomationRule;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Models\AutomationRule;
use App\Http\Controllers\Controller;
use App\Http\Requests\Automation\UpsertAutomationRuleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AutomationRuleController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AutomationRule::class);

        $rules = AutomationRule::query()
            ->when($request->string('trigger')->toString(), fn ($q, $t) => $q->where('trigger', $t))
            ->when($request->has('enabled'), fn ($q) => $q->where('is_enabled', $request->boolean('enabled')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Automation/Rules/Index', [
            'rules' => $rules,
            'filters' => [
                'trigger' => $request->string('trigger')->toString(),
                'enabled' => $request->input('enabled'),
            ],
            'triggers' => AutomationTrigger::options(),
            'can' => [
                'create' => $request->user()?->can('automation.create') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AutomationRule::class);

        return Inertia::render('Automation/Rules/Form', [
            'rule' => null,
            'triggers' => AutomationTrigger::options(),
        ]);
    }

    public function store(UpsertAutomationRuleRequest $request, UpsertAutomationRule $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $data['created_by'] = $request->user()->id;

        $rule = $action->handle(null, $data);

        return redirect()->route('automation.rules.show', $rule)->with('success', 'Automation rule created.');
    }

    public function show(AutomationRule $automation_rule): Response
    {
        $this->authorize('view', $automation_rule);

        return Inertia::render('Automation/Rules/Show', [
            'rule' => [
                ...$automation_rule->toArray(),
                'trigger' => $automation_rule->getRawOriginal('trigger'),
                'last_status' => $automation_rule->last_status?->value,
            ],
            'recentRuns' => $automation_rule->runs()->limit(20)->get()->map(fn ($r) => [
                ...$r->toArray(),
                'trigger' => $r->getRawOriginal('trigger'),
                'status' => $r->getRawOriginal('status'),
            ]),
            'can' => [
                'update' => request()->user()?->can('update', $automation_rule) ?? false,
                'delete' => request()->user()?->can('delete', $automation_rule) ?? false,
                'run' => request()->user()?->can('run', $automation_rule) ?? false,
            ],
        ]);
    }

    public function edit(AutomationRule $automation_rule): Response
    {
        $this->authorize('update', $automation_rule);

        return Inertia::render('Automation/Rules/Form', [
            'rule' => [
                ...$automation_rule->toArray(),
                'trigger' => $automation_rule->getRawOriginal('trigger'),
            ],
            'triggers' => AutomationTrigger::options(),
        ]);
    }

    public function update(UpsertAutomationRuleRequest $request, AutomationRule $automation_rule, UpsertAutomationRule $action): RedirectResponse
    {
        $action->handle($automation_rule, $request->validated());

        return redirect()->route('automation.rules.show', $automation_rule)->with('success', 'Automation rule updated.');
    }

    public function destroy(AutomationRule $automation_rule, DeleteAutomationRule $action): RedirectResponse
    {
        $this->authorize('delete', $automation_rule);
        $action->handle($automation_rule);

        return redirect()->route('automation.rules.index')->with('success', 'Automation rule deleted.');
    }

    public function toggle(AutomationRule $automation_rule, ToggleAutomationRule $action): RedirectResponse
    {
        $this->authorize('update', $automation_rule);
        $action->handle($automation_rule);

        return back()->with('success', 'Rule updated.');
    }

    public function run(AutomationRule $automation_rule, RunAutomationRuleNow $action, Request $request): RedirectResponse
    {
        $this->authorize('run', $automation_rule);
        $action->handle($automation_rule, $request->user());

        return back()->with('success', 'Rule executed.');
    }
}
