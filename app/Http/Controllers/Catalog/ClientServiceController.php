<?php

namespace App\Http\Controllers\Catalog;

use App\Domains\Catalog\Actions\DeleteClientService;
use App\Domains\Catalog\Actions\UpsertClientService;
use App\Domains\Catalog\Enums\BillingCycle;
use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Enums\ClientServiceStatus;
use App\Domains\Catalog\Models\ClientService;
use App\Domains\Catalog\Models\ServiceType;
use App\Domains\Crm\Models\Client;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\UpsertClientServiceRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ClientService::class);

        $services = ClientService::query()
            ->with(['client:id,company_name', 'serviceType:id,name,code'])
            ->when($request->string('search')->toString(), function ($query, string $search) {
                $query->where('name', 'ilike', "%{$search}%");
            })
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('client_id')->toString(), fn ($q, $id) => $q->where('client_id', $id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Finance/ClientServices/Index', [
            'services' => $services,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'client_id' => $request->string('client_id')->toString(),
            ],
            'statuses' => ClientServiceStatus::options(),
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'can' => [
                'create' => $request->user()?->can('client_services.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', ClientService::class);

        return Inertia::render('Finance/ClientServices/Form', [
            'service' => null,
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'serviceTypes' => ServiceType::query()->with('category:id,name')->orderBy('name')->get([
                'id', 'name', 'code', 'service_category_id', 'billing_type', 'billing_cycle',
                'default_cost', 'default_selling_price',
            ]),
            'billingTypes' => BillingType::options(),
            'billingCycles' => BillingCycle::options(),
            'statuses' => ClientServiceStatus::options(),
            'users' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'preselectedClientId' => $request->string('client_id')->toString() ?: null,
        ]);
    }

    public function store(UpsertClientServiceRequest $request, UpsertClientService $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $service = $action->handle(null, $data);

        return redirect()
            ->route('client-services.index')
            ->with('success', 'Client service created.');
    }

    public function edit(Request $request, ClientService $clientService): Response
    {
        $this->authorize('update', $clientService);

        return Inertia::render('Finance/ClientServices/Form', [
            'service' => [
                ...$clientService->toArray(),
                'billing_type' => $clientService->getRawOriginal('billing_type'),
                'billing_cycle' => $clientService->getRawOriginal('billing_cycle'),
                'status' => $clientService->getRawOriginal('status'),
                'start_date' => $clientService->start_date?->toDateString(),
                'end_date' => $clientService->end_date?->toDateString(),
                'renewal_date' => $clientService->renewal_date?->toDateString(),
            ],
            'clients' => Client::query()->orderBy('company_name')->get(['id', 'company_name']),
            'serviceTypes' => ServiceType::query()->with('category:id,name')->orderBy('name')->get([
                'id', 'name', 'code', 'service_category_id', 'billing_type', 'billing_cycle',
                'default_cost', 'default_selling_price',
            ]),
            'billingTypes' => BillingType::options(),
            'billingCycles' => BillingCycle::options(),
            'statuses' => ClientServiceStatus::options(),
            'users' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'preselectedClientId' => $clientService->client_id,
        ]);
    }

    public function update(
        UpsertClientServiceRequest $request,
        ClientService $clientService,
        UpsertClientService $action
    ): RedirectResponse {
        $action->handle($clientService, $request->validated());

        return redirect()
            ->route('client-services.index')
            ->with('success', 'Client service updated.');
    }

    public function destroy(ClientService $clientService, DeleteClientService $action): RedirectResponse
    {
        $this->authorize('delete', $clientService);
        $action->handle($clientService);

        return redirect()
            ->route('client-services.index')
            ->with('success', 'Client service deleted.');
    }
}
