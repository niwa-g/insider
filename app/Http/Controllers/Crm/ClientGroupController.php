<?php

namespace App\Http\Controllers\Crm;

use App\Domains\Crm\Actions\UpsertClientGroup;
use App\Domains\Crm\Models\Client;
use App\Domains\Crm\Models\ClientGroup;
use App\Domains\Platform\Services\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\UpsertClientGroupRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ClientGroupController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ClientGroup::class);

        $groups = ClientGroup::query()
            ->withCount('clients')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Crm/ClientGroups/Index', [
            'groups' => $groups,
            'can' => [
                'create' => $request->user()?->can('client_groups.create') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ClientGroup::class);

        return Inertia::render('Crm/ClientGroups/Form', [
            'group' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
        ]);
    }

    public function store(UpsertClientGroupRequest $request, UpsertClientGroup $action): RedirectResponse
    {
        $data = $request->safe()->except('client_ids');
        $data['organization_id'] = $request->user()->organization_id;

        $action->handle(null, $data, $request->input('client_ids', []));

        return redirect()
            ->route('client-groups.index')
            ->with('success', 'Client group created.');
    }

    public function edit(ClientGroup $clientGroup): Response
    {
        $this->authorize('update', $clientGroup);

        $clientGroup->load('clients:id');

        return Inertia::render('Crm/ClientGroups/Form', [
            'group' => [
                ...$clientGroup->toArray(),
                'client_ids' => $clientGroup->clients->pluck('id'),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
        ]);
    }

    public function update(
        UpsertClientGroupRequest $request,
        ClientGroup $clientGroup,
        UpsertClientGroup $action
    ): RedirectResponse {
        $data = $request->safe()->except('client_ids');
        $action->handle($clientGroup, $data, $request->input('client_ids', []));

        return redirect()
            ->route('client-groups.index')
            ->with('success', 'Client group updated.');
    }

    public function destroy(ClientGroup $clientGroup, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $clientGroup);

        DB::transaction(function () use ($clientGroup, $auditLogger) {
            $snapshot = $clientGroup->toArray();
            $clientGroup->clients()->detach();
            $clientGroup->delete();
            $auditLogger->log('client_group.deleted', $clientGroup, $snapshot, null);
        });

        return redirect()
            ->route('client-groups.index')
            ->with('success', 'Client group deleted.');
    }
}
