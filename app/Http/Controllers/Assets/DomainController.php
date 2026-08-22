<?php

namespace App\Http\Controllers\Assets;

use App\Domains\Assets\Actions\DeleteDomain;
use App\Domains\Assets\Actions\UpsertDomain;
use App\Domains\Assets\Enums\DomainStatus;
use App\Domains\Assets\Models\Domain;
use App\Domains\Assets\Models\Provider;
use App\Domains\Crm\Models\Client;
use App\Domains\Delivery\Models\Project;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\UpsertDomainRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DomainController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Domain::class);

        $domains = Domain::query()
            ->with(['client:id,company_name', 'provider:id,name'])
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Assets/Domains/Index', [
            'domains' => $domains,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'client_id' => $request->string('client_id')->toString(),
            ],
            'statuses' => DomainStatus::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'can' => ['create' => $request->user()?->can('domains.create') ?? false],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Domain::class);

        return Inertia::render('Assets/Domains/Form', [
            'domain' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'providers' => Provider::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => DomainStatus::options(),
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertDomainRequest $request, UpsertDomain $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $domain = $action->handle(null, $data);

        return redirect()->route('domains.show', $domain)->with('success', 'Domain created.');
    }

    public function show(Request $request, Domain $domain): Response
    {
        $this->authorize('view', $domain);

        $domain->load([
            'client:id,company_name',
            'provider:id,name',
            'dnsRecords' => fn ($q) => $q->orderBy('type')->orderBy('name'),
            'sslCertificates' => fn ($q) => $q->orderBy('expires_at'),
        ]);

        return Inertia::render('Assets/Domains/Show', [
            'domain' => [
                ...$domain->toArray(),
                'status' => $domain->getRawOriginal('status'),
                'registered_at' => $domain->registered_at?->toDateString(),
                'expires_at' => $domain->expires_at?->toDateString(),
            ],
            'can' => [
                'update' => $request->user()?->can('update', $domain) ?? false,
                'delete' => $request->user()?->can('delete', $domain) ?? false,
                'createDns' => $request->user()?->can('domains.create') ?? false,
            ],
        ]);
    }

    public function edit(Domain $domain): Response
    {
        $this->authorize('update', $domain);

        return Inertia::render('Assets/Domains/Form', [
            'domain' => [
                ...$domain->toArray(),
                'status' => $domain->getRawOriginal('status'),
                'registered_at' => $domain->registered_at?->toDateString(),
                'expires_at' => $domain->expires_at?->toDateString(),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name', 'client_id']),
            'providers' => Provider::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => DomainStatus::options(),
            'preselectedClientId' => $domain->client_id,
        ]);
    }

    public function update(UpsertDomainRequest $request, Domain $domain, UpsertDomain $action): RedirectResponse
    {
        $action->handle($domain, $request->validated());

        return redirect()->route('domains.show', $domain)->with('success', 'Domain updated.');
    }

    public function destroy(Domain $domain, DeleteDomain $action): RedirectResponse
    {
        $this->authorize('delete', $domain);
        $action->handle($domain);

        return redirect()->route('domains.index')->with('success', 'Domain deleted.');
    }
}
