<?php

namespace App\Http\Controllers\Delivery;

use App\Domains\Catalog\Models\ClientService;
use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Actions\ApplyProjectTemplate;
use App\Domains\Delivery\Actions\DeleteProject;
use App\Domains\Delivery\Actions\SyncProjectServices;
use App\Domains\Delivery\Actions\UpsertProject;
use App\Domains\Delivery\Enums\ProjectHealthStatus;
use App\Domains\Delivery\Enums\ProjectPriority;
use App\Domains\Delivery\Enums\ProjectStatus;
use App\Domains\Delivery\Enums\TaskStatus;
use App\Domains\Delivery\Models\Project;
use App\Domains\Delivery\Models\ProjectTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\SyncProjectServicesRequest;
use App\Http\Requests\Delivery\UpsertProjectRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Project::class);

        $status = $request->string('status')->toString();

        $projects = Project::query()
            ->with(['client:id,company_name', 'manager:id,name'])
            ->withCount([
                'tasks',
                'tasks as open_tasks_count' => fn ($q) => $q->whereNotIn('status', [
                    TaskStatus::Done->value,
                    TaskStatus::Cancelled->value,
                ]),
            ])
            ->when($request->string('search')->toString(), function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('code', 'ilike', "%{$search}%");
                });
            })
            ->when($status, fn ($q, $value) => $q->where('status', $value))
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->when($request->string('health_status')->toString(), fn ($q, $value) => $q->where('health_status', $value))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Delivery/Projects/Index', [
            'projects' => $projects,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $status,
                'client_id' => $request->string('client_id')->toString(),
                'health_status' => $request->string('health_status')->toString(),
            ],
            'statuses' => ProjectStatus::options(),
            'healthStatuses' => ProjectHealthStatus::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'can' => [
                'create' => $request->user()?->can('projects.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Project::class);

        return Inertia::render('Delivery/Projects/Form', [
            'project' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'statuses' => ProjectStatus::options(),
            'priorities' => ProjectPriority::options(),
            'managers' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'templates' => ProjectTemplate::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'clientServices' => [],
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(
        UpsertProjectRequest $request,
        UpsertProject $upsert,
        ApplyProjectTemplate $applyTemplate,
        SyncProjectServices $syncServices,
    ): RedirectResponse {
        $data = $request->safe()->except(['client_service_ids', 'apply_template', 'project_template_id']);
        $data['organization_id'] = $request->user()->organization_id;

        $project = $upsert->handle(null, $data);

        if ($request->boolean('apply_template') && $request->string('project_template_id')->toString()) {
            $template = ProjectTemplate::query()->findOrFail($request->string('project_template_id')->toString());
            $applyTemplate->handle($project, $template);
        } elseif ($request->filled('project_template_id')) {
            $project->update(['project_template_id' => $request->string('project_template_id')->toString()]);
        }

        if ($request->filled('client_service_ids')) {
            $syncServices->handle($project, $request->input('client_service_ids', []));
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created.');
    }

    public function show(Request $request, Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load([
            'client:id,company_name,client_code',
            'manager:id,name,email',
            'template:id,name',
            'services.clientService:id,name,status,selling_price',
            'tasks' => fn ($q) => $q->with('assignee:id,name')->orderBy('sort_order')->orderBy('due_date'),
        ]);

        $openTasks = $project->tasks->filter(fn ($task) => $task->status->isOpen())->count();
        $overdueTasks = $project->tasks
            ->filter(fn ($task) => $task->status->isOpen() && $task->due_date?->isPast())
            ->count();

        return Inertia::render('Delivery/Projects/Show', [
            'project' => [
                ...$project->toArray(),
                'status' => $project->getRawOriginal('status'),
                'priority' => $project->getRawOriginal('priority'),
                'health_status' => $project->getRawOriginal('health_status'),
                'start_date' => $project->start_date?->toDateString(),
                'due_date' => $project->due_date?->toDateString(),
            ],
            'stats' => [
                'tasks' => $project->tasks->count(),
                'open_tasks' => $openTasks,
                'overdue_tasks' => $overdueTasks,
                'services' => $project->services->count(),
            ],
            'can' => [
                'update' => $request->user()?->can('update', $project) ?? false,
                'delete' => $request->user()?->can('delete', $project) ?? false,
                'manageTasks' => $request->user()?->can('tasks.create') ?? false,
            ],
        ]);
    }

    public function edit(Request $request, Project $project): Response
    {
        $this->authorize('update', $project);

        $project->load('services:id,project_id,client_service_id');

        return Inertia::render('Delivery/Projects/Form', [
            'project' => [
                ...$project->toArray(),
                'status' => $project->getRawOriginal('status'),
                'priority' => $project->getRawOriginal('priority'),
                'start_date' => $project->start_date?->toDateString(),
                'due_date' => $project->due_date?->toDateString(),
                'client_service_ids' => $project->services->pluck('client_service_id'),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'statuses' => ProjectStatus::options(),
            'priorities' => ProjectPriority::options(),
            'managers' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'templates' => ProjectTemplate::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'clientServices' => ClientService::query()
                ->where('client_id', $project->client_id)
                ->orderBy('name')
                ->get(['id', 'name', 'status']),
            'preselectedClientId' => $project->client_id,
        ]);
    }

    public function update(
        UpsertProjectRequest $request,
        Project $project,
        UpsertProject $upsert,
        SyncProjectServices $syncServices,
    ): RedirectResponse {
        $data = $request->safe()->except(['client_service_ids', 'apply_template', 'project_template_id']);
        $upsert->handle($project, $data);

        if ($request->has('client_service_ids')) {
            $syncServices->handle($project->fresh(), $request->input('client_service_ids', []));
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project, DeleteProject $action): RedirectResponse
    {
        $this->authorize('delete', $project);
        $action->handle($project);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted.');
    }

    public function syncServices(
        SyncProjectServicesRequest $request,
        Project $project,
        SyncProjectServices $action,
    ): RedirectResponse {
        $action->handle($project, $request->input('client_service_ids', []));

        return back()->with('success', 'Project services updated.');
    }
}
