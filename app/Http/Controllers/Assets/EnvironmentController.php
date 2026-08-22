<?php

namespace App\Http\Controllers\Assets;

use App\Domains\Assets\Actions\DeleteEnvironment;
use App\Domains\Assets\Actions\SyncEnvironmentResources;
use App\Domains\Assets\Actions\UpsertEnvironment;
use App\Domains\Assets\Enums\EnvironmentSlug;
use App\Domains\Assets\Enums\EnvironmentStatus;
use App\Domains\Assets\Models\Application;
use App\Domains\Assets\Models\Environment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\SyncEnvironmentResourcesRequest;
use App\Http\Requests\Assets\UpsertEnvironmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EnvironmentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Environment::class);

        $environments = Environment::query()
            ->with(['application:id,name,client_id', 'application.client:id,company_name'])
            ->when($request->string('application_id')->toString(), fn ($q, $id) => $q->where('application_id', $id))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Assets/Environments/Index', [
            'environments' => $environments,
            'filters' => [
                'application_id' => $request->string('application_id')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'statuses' => EnvironmentStatus::options(),
            'applications' => Application::query()->orderBy('name')->get(['id', 'name']),
            'can' => [
                'create' => $request->user()?->can('environments.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Environment::class);

        return Inertia::render('Assets/Environments/Form', [
            'environment' => null,
            'applications' => Application::query()->orderBy('name')->get(['id', 'name']),
            'slugs' => EnvironmentSlug::options(),
            'statuses' => EnvironmentStatus::options(),
            'preselectedApplicationId' => $request->string('application_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertEnvironmentRequest $request, UpsertEnvironment $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('environments.index')->with('success', 'Environment created.');
    }

    public function edit(Environment $environment): Response
    {
        $this->authorize('update', $environment);

        return Inertia::render('Assets/Environments/Form', [
            'environment' => [
                ...$environment->toArray(),
                'slug' => $environment->getRawOriginal('slug'),
                'status' => $environment->getRawOriginal('status'),
            ],
            'applications' => Application::query()->orderBy('name')->get(['id', 'name']),
            'slugs' => EnvironmentSlug::options(),
            'statuses' => EnvironmentStatus::options(),
            'preselectedApplicationId' => $environment->application_id,
        ]);
    }

    public function update(UpsertEnvironmentRequest $request, Environment $environment, UpsertEnvironment $action): RedirectResponse
    {
        $action->handle($environment, $request->validated());

        return redirect()->route('environments.index')->with('success', 'Environment updated.');
    }

    public function destroy(Environment $environment, DeleteEnvironment $action): RedirectResponse
    {
        $this->authorize('delete', $environment);
        $action->handle($environment);

        return redirect()->route('environments.index')->with('success', 'Environment deleted.');
    }

    public function syncResources(
        SyncEnvironmentResourcesRequest $request,
        Environment $environment,
        SyncEnvironmentResources $action,
    ): RedirectResponse {
        $action->handle($environment, $request->input('resources', []));

        return back()->with('success', 'Environment resources updated.');
    }
}
