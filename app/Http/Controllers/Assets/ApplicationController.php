<?php

namespace App\Http\Controllers\Assets;

use App\Domains\Assets\Actions\DeleteApplication;
use App\Domains\Assets\Actions\UpsertApplication;
use App\Domains\Assets\Enums\ApplicationStatus;
use App\Domains\Assets\Models\Application;
use App\Domains\Assets\Models\CloudResource;
use App\Domains\Assets\Models\Domain;
use App\Domains\Assets\Models\HostingAccount;
use App\Domains\Assets\Models\Server;
use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Models\Project;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\UpsertApplicationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Application::class);

        $applications = Application::query()
            ->with(['client:id,company_name', 'project:id,name', 'owner:id,name'])
            ->withCount('environments')
            ->when($request->string('search')->toString(), fn ($q, $search) => $q->where('name', 'ilike', "%{$search}%"))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Assets/Applications/Index', [
            'applications' => $applications,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'client_id' => $request->string('client_id')->toString(),
            ],
            'statuses' => ApplicationStatus::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'can' => [
                'create' => $request->user()?->can('applications.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Application::class);

        return Inertia::render('Assets/Applications/Form', [
            'application' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'statuses' => ApplicationStatus::options(),
            'owners' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertApplicationRequest $request, UpsertApplication $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $application = $action->handle(null, $data);

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Application created.');
    }

    public function show(Request $request, Application $application): Response
    {
        $this->authorize('view', $application);

        $application->load([
            'client:id,company_name',
            'project:id,name',
            'owner:id,name',
            'environments' => fn ($q) => $q->orderBy('name'),
        ]);

        return Inertia::render('Assets/Applications/Show', [
            'application' => [
                ...$application->toArray(),
                'status' => $application->getRawOriginal('status'),
            ],
            'can' => [
                'update' => $request->user()?->can('update', $application) ?? false,
                'delete' => $request->user()?->can('delete', $application) ?? false,
            ],
        ]);
    }

    public function relationships(Request $request, Application $application): Response
    {
        $this->authorize('view', $application);

        $application->load([
            'client:id,company_name',
            'environments.resources.resourceable',
        ]);

        $domains = Domain::query()
            ->where('client_id', $application->client_id)
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        $servers = Server::query()
            ->where('client_id', $application->client_id)
            ->orderBy('name')
            ->get(['id', 'name', 'hostname', 'status']);

        $hosting = HostingAccount::query()
            ->where('client_id', $application->client_id)
            ->orderBy('name')
            ->get(['id', 'name', 'primary_domain', 'status']);

        $cloud = CloudResource::query()
            ->where('client_id', $application->client_id)
            ->orderBy('name')
            ->get(['id', 'name', 'resource_type', 'status']);

        return Inertia::render('Assets/Applications/Relationships', [
            'application' => [
                'id' => $application->id,
                'name' => $application->name,
                'client' => $application->client,
                'environments' => $application->environments->map(fn ($env) => [
                    'id' => $env->id,
                    'name' => $env->name,
                    'slug' => $env->getRawOriginal('slug'),
                    'status' => $env->getRawOriginal('status'),
                    'resources' => $env->resources->map(fn ($r) => [
                        'id' => $r->id,
                        'role' => $r->getRawOriginal('role'),
                        'type' => class_basename($r->resourceable_type),
                        'name' => $r->resourceable?->name ?? '—',
                    ]),
                ]),
            ],
            'domains' => $domains->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'status' => $d->getRawOriginal('status'),
            ]),
            'servers' => $servers->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'hostname' => $s->hostname,
                'status' => $s->getRawOriginal('status'),
            ]),
            'hostingAccounts' => $hosting->map(fn ($h) => [
                'id' => $h->id,
                'name' => $h->name,
                'primary_domain' => $h->primary_domain,
                'status' => $h->getRawOriginal('status'),
            ]),
            'cloudResources' => $cloud->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'resource_type' => $c->getRawOriginal('resource_type'),
                'status' => $c->getRawOriginal('status'),
            ]),
        ]);
    }

    public function edit(Request $request, Application $application): Response
    {
        $this->authorize('update', $application);

        return Inertia::render('Assets/Applications/Form', [
            'application' => [
                ...$application->toArray(),
                'status' => $application->getRawOriginal('status'),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'statuses' => ApplicationStatus::options(),
            'owners' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'preselectedClientId' => $application->client_id,
        ]);
    }

    public function update(UpsertApplicationRequest $request, Application $application, UpsertApplication $action): RedirectResponse
    {
        $action->handle($application, $request->validated());

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Application updated.');
    }

    public function destroy(Application $application, DeleteApplication $action): RedirectResponse
    {
        $this->authorize('delete', $application);
        $action->handle($application);

        return redirect()
            ->route('applications.index')
            ->with('success', 'Application deleted.');
    }
}
