<?php

namespace App\Http\Controllers\Assets;

use App\Domains\Assets\Actions\DeleteHostingAccount;
use App\Domains\Assets\Actions\UpsertHostingAccount;
use App\Domains\Assets\Enums\HostingAccountStatus;
use App\Domains\Assets\Models\HostingAccount;
use App\Domains\Assets\Models\Provider;
use App\Domains\Crm\Models\Client;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\UpsertHostingAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HostingAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', HostingAccount::class);

        $accounts = HostingAccount::query()
            ->with(['client:id,company_name', 'provider:id,name'])
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Assets/HostingAccounts/Index', [
            'accounts' => $accounts,
            'filters' => ['client_id' => $request->string('client_id')->toString()],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'statuses' => HostingAccountStatus::options(),
            'can' => ['create' => $request->user()?->can('infrastructure.create') ?? false],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', HostingAccount::class);

        return Inertia::render('Assets/HostingAccounts/Form', [
            'account' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'providers' => Provider::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => HostingAccountStatus::options(),
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertHostingAccountRequest $request, UpsertHostingAccount $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('hosting-accounts.index')->with('success', 'Hosting account created.');
    }

    public function edit(HostingAccount $hostingAccount): Response
    {
        $this->authorize('update', $hostingAccount);

        return Inertia::render('Assets/HostingAccounts/Form', [
            'account' => [
                ...$hostingAccount->toArray(),
                'status' => $hostingAccount->getRawOriginal('status'),
                'expires_at' => $hostingAccount->expires_at?->toDateString(),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'providers' => Provider::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => HostingAccountStatus::options(),
            'preselectedClientId' => $hostingAccount->client_id,
        ]);
    }

    public function update(UpsertHostingAccountRequest $request, HostingAccount $hostingAccount, UpsertHostingAccount $action): RedirectResponse
    {
        $action->handle($hostingAccount, $request->validated());

        return redirect()->route('hosting-accounts.index')->with('success', 'Hosting account updated.');
    }

    public function destroy(HostingAccount $hostingAccount, DeleteHostingAccount $action): RedirectResponse
    {
        $this->authorize('delete', $hostingAccount);
        $action->handle($hostingAccount);

        return redirect()->route('hosting-accounts.index')->with('success', 'Hosting account deleted.');
    }
}
