<?php

namespace App\Http\Controllers\Catalog;

use App\Domains\Catalog\Actions\UpsertServiceType;
use App\Domains\Catalog\Enums\BillingCycle;
use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Enums\ServiceStatus;
use App\Domains\Catalog\Models\ServiceCategory;
use App\Domains\Catalog\Models\ServiceType;
use App\Domains\Platform\Services\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\UpsertServiceTypeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ServiceTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ServiceType::class);

        $types = ServiceType::query()
            ->with(['category:id,name', 'owner:id,name'])
            ->when($request->string('search')->toString(), function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('code', 'ilike', "%{$search}%");
                });
            })
            ->when($request->string('category_id')->toString(), fn ($q, $id) => $q->where('service_category_id', $id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Catalog/ServiceTypes/Index', [
            'serviceTypes' => $types,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'category_id' => $request->string('category_id')->toString(),
            ],
            'categories' => ServiceCategory::query()->orderBy('name')->get(['id', 'name']),
            'can' => [
                'create' => $request->user()?->can('service_catalog.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', ServiceType::class);

        return Inertia::render('Catalog/ServiceTypes/Form', [
            'serviceType' => null,
            'categories' => ServiceCategory::query()->orderBy('name')->get(['id', 'name']),
            'billingTypes' => BillingType::options(),
            'billingCycles' => BillingCycle::options(),
            'statuses' => ServiceStatus::options(),
            'owners' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(UpsertServiceTypeRequest $request, UpsertServiceType $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('service-types.index')->with('success', 'Service type created.');
    }

    public function edit(Request $request, ServiceType $serviceType): Response
    {
        $this->authorize('update', $serviceType);

        return Inertia::render('Catalog/ServiceTypes/Form', [
            'serviceType' => [
                ...$serviceType->toArray(),
                'billing_type' => $serviceType->getRawOriginal('billing_type'),
                'billing_cycle' => $serviceType->getRawOriginal('billing_cycle'),
                'status' => $serviceType->getRawOriginal('status'),
            ],
            'categories' => ServiceCategory::query()->orderBy('name')->get(['id', 'name']),
            'billingTypes' => BillingType::options(),
            'billingCycles' => BillingCycle::options(),
            'statuses' => ServiceStatus::options(),
            'owners' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(
        UpsertServiceTypeRequest $request,
        ServiceType $serviceType,
        UpsertServiceType $action
    ): RedirectResponse {
        $action->handle($serviceType, $request->validated());

        return redirect()->route('service-types.index')->with('success', 'Service type updated.');
    }

    public function destroy(ServiceType $serviceType, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $serviceType);

        DB::transaction(function () use ($serviceType, $auditLogger) {
            $snapshot = $serviceType->toArray();
            $serviceType->delete();
            $auditLogger->log('service_type.deleted', $serviceType, $snapshot, null);
        });

        return redirect()->route('service-types.index')->with('success', 'Service type deleted.');
    }
}
