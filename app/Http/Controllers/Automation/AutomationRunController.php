<?php

namespace App\Http\Controllers\Automation;

use App\Domains\Automation\Enums\AutomationRunStatus;
use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Models\AutomationRun;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AutomationRunController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AutomationRun::class);

        $runs = AutomationRun::query()
            ->with(['rule:id,name', 'schedule:id,name', 'triggeredBy:id,name'])
            ->when($request->string('trigger')->toString(), fn ($q, $t) => $q->where('trigger', $t))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Automation/Runs/Index', [
            'runs' => $runs,
            'filters' => [
                'trigger' => $request->string('trigger')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'triggers' => AutomationTrigger::options(),
            'statuses' => AutomationRunStatus::options(),
        ]);
    }

    public function show(AutomationRun $automation_run): Response
    {
        $this->authorize('view', $automation_run);

        $automation_run->load(['rule', 'schedule', 'triggeredBy:id,name']);

        return Inertia::render('Automation/Runs/Show', [
            'run' => [
                ...$automation_run->toArray(),
                'trigger' => $automation_run->getRawOriginal('trigger'),
                'status' => $automation_run->getRawOriginal('status'),
            ],
        ]);
    }
}
