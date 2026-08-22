<?php

namespace App\Http\Controllers\Delivery;

use App\Domains\Delivery\Actions\DeleteProjectTemplate;
use App\Domains\Delivery\Actions\UpsertProjectTemplate;
use App\Domains\Delivery\Enums\ProjectStatus;
use App\Domains\Delivery\Enums\TaskPriority;
use App\Domains\Delivery\Enums\TaskStatus;
use App\Domains\Delivery\Models\ProjectTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\UpsertProjectTemplateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectTemplateController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ProjectTemplate::class);

        $templates = ProjectTemplate::query()
            ->withCount('items')
            ->when($request->string('search')->toString(), function ($q, string $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'ilike', "%{$search}%")
                        ->orWhere('code', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Delivery/ProjectTemplates/Index', [
            'templates' => $templates,
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
            'can' => [
                'create' => $request->user()?->can('project_templates.create') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ProjectTemplate::class);

        return Inertia::render('Delivery/ProjectTemplates/Form', [
            'template' => null,
            'statuses' => ProjectStatus::options(),
            'taskStatuses' => TaskStatus::options(),
            'priorities' => TaskPriority::options(),
        ]);
    }

    public function store(UpsertProjectTemplateRequest $request, UpsertProjectTemplate $action): RedirectResponse
    {
        $data = $request->safe()->except('items');
        $data['organization_id'] = $request->user()->organization_id;
        $data['is_active'] = $request->boolean('is_active', true);

        $action->handle(null, $data, $request->input('items', []));

        return redirect()
            ->route('project-templates.index')
            ->with('success', 'Template created.');
    }

    public function edit(ProjectTemplate $projectTemplate): Response
    {
        $this->authorize('update', $projectTemplate);

        $projectTemplate->load('items');

        return Inertia::render('Delivery/ProjectTemplates/Form', [
            'template' => [
                ...$projectTemplate->toArray(),
                'default_status' => $projectTemplate->getRawOriginal('default_status'),
                'items' => $projectTemplate->items->map(fn ($item) => [
                    ...$item->toArray(),
                    'status' => $item->getRawOriginal('status'),
                    'priority' => $item->getRawOriginal('priority'),
                ]),
            ],
            'statuses' => ProjectStatus::options(),
            'taskStatuses' => TaskStatus::options(),
            'priorities' => TaskPriority::options(),
        ]);
    }

    public function update(
        UpsertProjectTemplateRequest $request,
        ProjectTemplate $projectTemplate,
        UpsertProjectTemplate $action,
    ): RedirectResponse {
        $data = $request->safe()->except('items');
        $data['is_active'] = $request->boolean('is_active', true);
        $action->handle($projectTemplate, $data, $request->input('items', []));

        return redirect()
            ->route('project-templates.index')
            ->with('success', 'Template updated.');
    }

    public function destroy(ProjectTemplate $projectTemplate, DeleteProjectTemplate $action): RedirectResponse
    {
        $this->authorize('delete', $projectTemplate);
        $action->handle($projectTemplate);

        return redirect()
            ->route('project-templates.index')
            ->with('success', 'Template deleted.');
    }
}
