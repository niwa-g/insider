<?php

namespace App\Http\Controllers\Integrations;

use App\Domains\Integrations\Actions\DeleteIntegration;
use App\Domains\Integrations\Actions\UpsertIntegration;
use App\Domains\Integrations\Enums\IntegrationCategory;
use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Enums\IntegrationStatus;
use App\Domains\Integrations\Models\Integration;
use App\Domains\Integrations\Services\CredentialVault;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\UpsertIntegrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Integration::class);

        $integrations = Integration::query()
            ->when($request->string('category')->toString(), fn ($q, $category) => $q->where('category', $category))
            ->latest()
            ->get()
            ->map(fn (Integration $integration) => app(CredentialVault::class)->toPublicArray($integration));

        return Inertia::render('Integrations/Index', [
            'integrations' => $integrations,
            'filters' => ['category' => $request->string('category')->toString()],
            'providers' => IntegrationProvider::options(),
            'categories' => IntegrationCategory::options(),
            'statuses' => IntegrationStatus::options(),
            'can' => [
                'create' => $request->user()?->can('integrations.create') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Integration::class);

        return Inertia::render('Integrations/Form', [
            'integration' => null,
            'providers' => IntegrationProvider::options(),
            'categories' => IntegrationCategory::options(),
            'hasCredentials' => false,
        ]);
    }

    public function store(UpsertIntegrationRequest $request, UpsertIntegration $action): RedirectResponse
    {
        $data = $request->validated();
        unset($data['credentials']);
        $data['organization_id'] = $request->user()->organization_id;

        $integration = $action->handle(null, $data, $request->credentialsPayload());

        return redirect()->route('integrations.show', $integration)->with('success', 'Integration created.');
    }

    public function show(Integration $integration): Response
    {
        $this->authorize('view', $integration);

        $vault = app(CredentialVault::class);

        return Inertia::render('Integrations/Show', [
            'integration' => $vault->toPublicArray($integration->load('connectedBy:id,name')),
            'externalRefs' => $integration->externalRefs()->latest('last_seen_at')->limit(50)->get(),
            'syncLogs' => $integration->syncLogs()->limit(20)->get(),
            'hasCredentials' => $vault->hasCredentials($integration),
            'can' => [
                'update' => request()->user()?->can('update', $integration) ?? false,
                'delete' => request()->user()?->can('delete', $integration) ?? false,
                'sync' => request()->user()?->can('sync', $integration) ?? false,
            ],
        ]);
    }

    public function edit(Integration $integration): Response
    {
        $this->authorize('update', $integration);

        $vault = app(CredentialVault::class);

        return Inertia::render('Integrations/Form', [
            'integration' => [
                ...$vault->toPublicArray($integration),
                'provider' => $integration->getRawOriginal('provider'),
                'category' => $integration->getRawOriginal('category'),
            ],
            'providers' => IntegrationProvider::options(),
            'categories' => IntegrationCategory::options(),
            'hasCredentials' => $vault->hasCredentials($integration),
        ]);
    }

    public function update(UpsertIntegrationRequest $request, Integration $integration, UpsertIntegration $action): RedirectResponse
    {
        $data = $request->validated();
        unset($data['credentials']);

        $action->handle($integration, $data, $request->credentialsPayload());

        return redirect()->route('integrations.show', $integration)->with('success', 'Integration updated.');
    }

    public function destroy(Integration $integration, DeleteIntegration $action): RedirectResponse
    {
        $this->authorize('delete', $integration);
        $action->handle($integration);

        return redirect()->route('integrations.index')->with('success', 'Integration deleted.');
    }
}
