<?php

namespace App\Http\Controllers\Crm;

use App\Domains\Crm\Actions\CreateClient;
use App\Domains\Crm\Actions\DeleteClient;
use App\Domains\Crm\Actions\UpdateClient;
use App\Domains\Crm\Enums\ClientStatus;
use App\Domains\Crm\Models\Client;
use App\Domains\Crm\Models\ClientGroup;
use App\Domains\Crm\Models\Contact;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreClientRequest;
use App\Http\Requests\Crm\UpdateClientRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::query()
            ->with(['accountManager:id,name', 'groups:id,name'])
            ->withCount('contacts')
            ->when($request->string('search')->toString(), function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'ilike', "%{$search}%")
                        ->orWhere('client_code', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Crm/Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'statuses' => ClientStatus::options(),
            'can' => [
                'create' => $request->user()?->can('clients.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('Crm/Clients/Form', [
            'client' => null,
            'statuses' => ClientStatus::options(),
            'groups' => ClientGroup::query()->orderBy('name')->get(['id', 'name']),
            'managers' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(StoreClientRequest $request, CreateClient $action): RedirectResponse
    {
        $data = $request->safe()->except('group_ids');
        $data['organization_id'] = $request->user()->organization_id;

        $client = $action->handle($data, $request->input('group_ids', []));

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client created.');
    }

    public function show(Request $request, Client $client): Response
    {
        $this->authorize('view', $client);

        $client->load([
            'accountManager:id,name,email',
            'groups:id,name,color',
            'contacts' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('name'),
        ]);

        $activities = Activity::query()
            ->where(function ($q) use ($client) {
                $q->where(function ($inner) use ($client) {
                    $inner->where('subject_type', Client::class)
                        ->where('subject_id', $client->id);
                })->orWhere(function ($inner) use ($client) {
                    $inner->where('subject_type', Contact::class)
                        ->whereIn('subject_id', $client->contacts->pluck('id'));
                });
            })
            ->latest()
            ->limit(25)
            ->get(['id', 'description', 'created_at', 'subject_type', 'event']);

        $related = [
            'projects' => $client->projects()
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'status', 'health_status']),
            'services' => $client->services()
                ->with('serviceType:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'status', 'billing_cycle', 'selling_price', 'service_type_id']),
            'domains' => $client->domains()
                ->orderBy('name')
                ->get(['id', 'name', 'status', 'expires_at']),
            'hostingAccounts' => $client->hostingAccounts()
                ->orderBy('name')
                ->get(['id', 'name', 'plan', 'status', 'primary_domain']),
            'applications' => $client->applications()
                ->orderBy('name')
                ->get(['id', 'name', 'status', 'stack']),
            'servers' => $client->servers()
                ->orderBy('name')
                ->get(['id', 'name', 'hostname', 'status', 'role']),
            'cloudResources' => $client->cloudResources()
                ->orderBy('name')
                ->get(['id', 'name', 'resource_type', 'status', 'region']),
            'tickets' => $client->tickets()
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(['id', 'number', 'subject', 'status', 'priority']),
            'documents' => $client->documents()
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(['id', 'title', 'status', 'original_name', 'created_at']),
        ];

        return Inertia::render('Crm/Clients/Show', [
            'client' => $client,
            'activities' => $activities,
            'related' => $related,
            'summary' => [
                'contacts' => $client->contacts->count(),
                'projects' => count($related['projects']),
                'services' => count($related['services']),
                'domains' => count($related['domains']),
                'hosting' => count($related['hostingAccounts']),
                'applications' => count($related['applications']),
                'infrastructure' => count($related['servers']) + count($related['cloudResources']),
                'tickets' => count($related['tickets']),
                'documents' => count($related['documents']),
            ],
            'can' => [
                'update' => $request->user()?->can('update', $client) ?? false,
                'delete' => $request->user()?->can('delete', $client) ?? false,
                'createContact' => $request->user()?->can('contacts.create') ?? false,
            ],
        ]);
    }

    public function edit(Request $request, Client $client): Response
    {
        $this->authorize('update', $client);

        $client->load('groups:id');

        $payload = $client->toArray();
        $payload['group_ids'] = $client->groups->pluck('id');
        $payload['status'] = $client->getRawOriginal('status');

        return Inertia::render('Crm/Clients/Form', [
            'client' => $payload,
            'statuses' => ClientStatus::options(),
            'groups' => ClientGroup::query()->orderBy('name')->get(['id', 'name']),
            'managers' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client, UpdateClient $action): RedirectResponse
    {
        $data = $request->safe()->except('group_ids');
        $action->handle($client, $data, $request->input('group_ids', []));

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client updated.');
    }

    public function destroy(Client $client, DeleteClient $action): RedirectResponse
    {
        $this->authorize('delete', $client);
        $action->handle($client);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client deleted.');
    }
}
