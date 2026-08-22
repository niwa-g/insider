<?php

namespace App\Http\Controllers\Delivery;

use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Actions\DeleteTask;
use App\Domains\Delivery\Actions\UpdateTaskStatus;
use App\Domains\Delivery\Actions\UpsertTask;
use App\Domains\Delivery\Enums\TaskPriority;
use App\Domains\Delivery\Enums\TaskStatus;
use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\Task;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\UpdateTaskStatusRequest;
use App\Http\Requests\Delivery\UpsertTaskRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        $view = $request->string('view')->toString() ?: 'list';

        $query = Task::query()
            ->with([
                'client:id,company_name',
                'project:id,name,code',
                'assignee:id,name',
            ])
            ->when($request->string('search')->toString(), function ($q, string $search) {
                $q->where('title', 'ilike', "%{$search}%");
            })
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('project_id')->toString(), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->string('assignee_id')->toString(), fn ($q, $id) => $q->where('assignee_id', $id));

        if ($view === 'board') {
            $tasks = $query->orderBy('sort_order')->orderBy('due_date')->get();
            $columns = collect(TaskStatus::boardColumns())->mapWithKeys(
                fn (string $status) => [
                    $status => $tasks->filter(fn (Task $task) => $task->status->value === $status)->values(),
                ]
            );
        } else {
            $columns = null;
            $tasks = $query->latest()->paginate(25)->withQueryString();
        }

        return Inertia::render('Delivery/Tasks/Index', [
            'tasks' => $view === 'board' ? null : $tasks,
            'columns' => $columns,
            'view' => $view,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'project_id' => $request->string('project_id')->toString(),
                'client_id' => $request->string('client_id')->toString(),
                'assignee_id' => $request->string('assignee_id')->toString(),
                'view' => $view,
            ],
            'statuses' => TaskStatus::options(),
            'priorities' => TaskPriority::options(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'code']),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'assignees' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'can' => [
                'create' => $request->user()?->can('tasks.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Task::class);

        $projectId = $request->string('project_id')->toString() ?: null;
        $project = $projectId ? Project::query()->find($projectId) : null;

        return Inertia::render('Delivery/Tasks/Form', [
            'task' => null,
            'statuses' => TaskStatus::options(),
            'priorities' => TaskPriority::options(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'code', 'client_id']),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'assignees' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'preselectedProjectId' => $project?->id,
            'preselectedClientId' => $project?->client_id ?? $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertTaskRequest $request, UpsertTask $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $task = $action->handle(null, $data);

        return redirect()
            ->route($task->project_id ? 'projects.show' : 'tasks.index', $task->project_id ? [$task->project_id] : [])
            ->with('success', 'Task created.');
    }

    public function edit(Request $request, Task $task): Response
    {
        $this->authorize('update', $task);

        return Inertia::render('Delivery/Tasks/Form', [
            'task' => [
                ...$task->toArray(),
                'status' => $task->getRawOriginal('status'),
                'priority' => $task->getRawOriginal('priority'),
                'due_date' => $task->due_date?->toDateString(),
            ],
            'statuses' => TaskStatus::options(),
            'priorities' => TaskPriority::options(),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'code', 'client_id']),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'assignees' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'preselectedProjectId' => $task->project_id,
            'preselectedClientId' => $task->client_id,
        ]);
    }

    public function update(UpsertTaskRequest $request, Task $task, UpsertTask $action): RedirectResponse
    {
        $action->handle($task, $request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task updated.');
    }

    public function destroy(Task $task, DeleteTask $action): RedirectResponse
    {
        $this->authorize('delete', $task);
        $action->handle($task);

        return back()->with('success', 'Task deleted.');
    }

    public function updateStatus(
        UpdateTaskStatusRequest $request,
        Task $task,
        UpdateTaskStatus $action,
    ): RedirectResponse {
        $action->handle(
            $task,
            $request->string('status')->toString(),
            $request->has('sort_order') ? (int) $request->input('sort_order') : null,
        );

        return back()->with('success', 'Task status updated.');
    }
}
