<?php

namespace App\Http\Controllers\Automation;

use App\Domains\Automation\Actions\DeleteAutomationSchedule;
use App\Domains\Automation\Actions\RunAutomationScheduleNow;
use App\Domains\Automation\Actions\ToggleAutomationSchedule;
use App\Domains\Automation\Actions\UpsertAutomationSchedule;
use App\Domains\Automation\Enums\AutomationScheduleJobType;
use App\Domains\Automation\Enums\ScheduleFrequency;
use App\Domains\Automation\Models\AutomationSchedule;
use App\Http\Controllers\Controller;
use App\Http\Requests\Automation\UpsertAutomationScheduleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AutomationScheduleController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AutomationSchedule::class);

        $schedules = AutomationSchedule::query()
            ->when($request->string('job_type')->toString(), fn ($q, $t) => $q->where('job_type', $t))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Automation/Schedules/Index', [
            'schedules' => $schedules,
            'filters' => ['job_type' => $request->string('job_type')->toString()],
            'jobTypes' => AutomationScheduleJobType::options(),
            'frequencies' => ScheduleFrequency::options(),
            'can' => [
                'create' => $request->user()?->can('automation.create') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AutomationSchedule::class);

        return Inertia::render('Automation/Schedules/Form', [
            'schedule' => null,
            'jobTypes' => AutomationScheduleJobType::options(),
            'frequencies' => ScheduleFrequency::options(),
        ]);
    }

    public function store(UpsertAutomationScheduleRequest $request, UpsertAutomationSchedule $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;

        $action->handle(null, $data);

        return redirect()->route('automation.schedules.index')->with('success', 'Schedule created.');
    }

    public function edit(AutomationSchedule $automation_schedule): Response
    {
        $this->authorize('update', $automation_schedule);

        return Inertia::render('Automation/Schedules/Form', [
            'schedule' => [
                ...$automation_schedule->toArray(),
                'job_type' => $automation_schedule->getRawOriginal('job_type'),
                'frequency' => $automation_schedule->getRawOriginal('frequency'),
                'last_status' => $automation_schedule->last_status?->value,
            ],
            'jobTypes' => AutomationScheduleJobType::options(),
            'frequencies' => ScheduleFrequency::options(),
        ]);
    }

    public function update(UpsertAutomationScheduleRequest $request, AutomationSchedule $automation_schedule, UpsertAutomationSchedule $action): RedirectResponse
    {
        $action->handle($automation_schedule, $request->validated());

        return redirect()->route('automation.schedules.index')->with('success', 'Schedule updated.');
    }

    public function destroy(AutomationSchedule $automation_schedule, DeleteAutomationSchedule $action): RedirectResponse
    {
        $this->authorize('delete', $automation_schedule);
        $action->handle($automation_schedule);

        return redirect()->route('automation.schedules.index')->with('success', 'Schedule deleted.');
    }

    public function toggle(AutomationSchedule $automation_schedule, ToggleAutomationSchedule $action): RedirectResponse
    {
        $this->authorize('update', $automation_schedule);
        $action->handle($automation_schedule);

        return back()->with('success', 'Schedule updated.');
    }

    public function run(AutomationSchedule $automation_schedule, RunAutomationScheduleNow $action, Request $request): RedirectResponse
    {
        $this->authorize('run', $automation_schedule);
        $action->handle($automation_schedule, $request->user());

        return back()->with('success', 'Schedule executed.');
    }
}
