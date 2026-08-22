<?php

namespace App\Http\Controllers\Assets;

use App\Domains\Assets\Actions\DeleteCloudResource;
use App\Domains\Assets\Actions\UpsertCloudResource;
use App\Domains\Assets\Enums\CloudResourceStatus;
use App\Domains\Assets\Enums\CloudResourceType;
use App\Domains\Assets\Models\CloudResource;
use App\Domains\Assets\Models\Provider;
use App\Domains\Crm\Models\Client;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\UpsertCloudResourceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CloudResourceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CloudResource::class);

        $resources = CloudResource::query()
            ->with(['client:id,company_name', 'provider:id,name'])
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Assets/CloudResources/Index', [
            'resources' => $resources,
            'filters' => ['client_id' => $request->string('client_id')->toString()],
            'statuses' => CloudResourceStatus::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'can' => ['create' => $request->user()?->can('infrastructure.create') ?? false],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', CloudResource::class);

        return Inertia::render('Assets/CloudResources/Form', [
            'resource' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'providers' => Provider::query()->orderBy('name')->get(['id', 'name']),
            'resourceTypes' => CloudResourceType::options(),
            'statuses' => CloudResourceStatus::options(),
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertCloudResourceRequest $request, UpsertCloudResource $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('cloud-resources.index')->with('success', 'Cloud resource created.');
    }

    public function edit(CloudResource $cloudResource): Response
    {
        $this->authorize('update', $cloudResource);

        return Inertia::render('Assets/CloudResources/Form', [
            'resource' => [
                ...$cloudResource->toArray(),
                'resource_type' => $cloudResource->getRawOriginal('resource_type'),
                'status' => $cloudResource->getRawOriginal('status'),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'providers' => Provider::query()->orderBy('name')->get(['id', 'name']),
            'resourceTypes' => CloudResourceType::options(),
            'statuses' => CloudResourceStatus::options(),
            'preselectedClientId' => $cloudResource->client_id,
        ]);
    }

    public function update(UpsertCloudResourceRequest $request, CloudResource $cloudResource, UpsertCloudResource $action): RedirectResponse
    {
        $action->handle($cloudResource, $request->validated());

        return redirect()->route('cloud-resources.index')->with('success', 'Cloud resource updated.');
    }

    public function destroy(CloudResource $cloudResource, DeleteCloudResource $action): RedirectResponse
    {
        $this->authorize('delete', $cloudResource);
        $action->handle($cloudResource);

        return redirect()->route('cloud-resources.index')->with('success', 'Cloud resource deleted.');
    }
}
