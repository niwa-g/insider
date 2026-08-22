<?php

namespace App\Http\Controllers\Assets;

use App\Domains\Assets\Actions\DeleteServer;
use App\Domains\Assets\Actions\UpsertServer;
use App\Domains\Assets\Enums\ServerRole;
use App\Domains\Assets\Enums\ServerStatus;
use App\Domains\Assets\Models\Provider;
use App\Domains\Assets\Models\Server;
use App\Domains\Crm\Models\Client;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\UpsertServerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Server::class);

        $servers = Server::query()
            ->with(['client:id,company_name', 'provider:id,name'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Assets/Servers/Index', [
            'servers' => $servers,
            'filters' => ['status' => $request->string('status')->toString()],
            'statuses' => ServerStatus::options(),
            'can' => ['create' => $request->user()?->can('infrastructure.create') ?? false],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Server::class);

        return Inertia::render('Assets/Servers/Form', [
            'server' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'providers' => Provider::query()->orderBy('name')->get(['id', 'name']),
            'roles' => ServerRole::options(),
            'statuses' => ServerStatus::options(),
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertServerRequest $request, UpsertServer $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('servers.index')->with('success', 'Server created.');
    }

    public function edit(Server $server): Response
    {
        $this->authorize('update', $server);

        return Inertia::render('Assets/Servers/Form', [
            'server' => [
                ...$server->toArray(),
                'role' => $server->getRawOriginal('role'),
                'status' => $server->getRawOriginal('status'),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'providers' => Provider::query()->orderBy('name')->get(['id', 'name']),
            'roles' => ServerRole::options(),
            'statuses' => ServerStatus::options(),
            'preselectedClientId' => $server->client_id,
        ]);
    }

    public function update(UpsertServerRequest $request, Server $server, UpsertServer $action): RedirectResponse
    {
        $action->handle($server, $request->validated());

        return redirect()->route('servers.index')->with('success', 'Server updated.');
    }

    public function destroy(Server $server, DeleteServer $action): RedirectResponse
    {
        $this->authorize('delete', $server);
        $action->handle($server);

        return redirect()->route('servers.index')->with('success', 'Server deleted.');
    }
}
