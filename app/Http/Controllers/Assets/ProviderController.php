<?php

namespace App\Http\Controllers\Assets;

use App\Domains\Assets\Actions\DeleteProvider;
use App\Domains\Assets\Actions\UpsertProvider;
use App\Domains\Assets\Enums\ProviderStatus;
use App\Domains\Assets\Enums\ProviderType;
use App\Domains\Assets\Models\Provider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\UpsertProviderRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Provider::class);

        $providers = Provider::query()
            ->when($request->string('type')->toString(), fn ($q, $type) => $q->where('type', $type))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Assets/Providers/Index', [
            'providers' => $providers,
            'filters' => [
                'type' => $request->string('type')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'types' => ProviderType::options(),
            'statuses' => ProviderStatus::options(),
            'can' => ['create' => $request->user()?->can('providers.create') ?? false],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Provider::class);

        return Inertia::render('Assets/Providers/Form', [
            'provider' => null,
            'types' => ProviderType::options(),
            'statuses' => ProviderStatus::options(),
        ]);
    }

    public function store(UpsertProviderRequest $request, UpsertProvider $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('providers.index')->with('success', 'Provider created.');
    }

    public function edit(Provider $provider): Response
    {
        $this->authorize('update', $provider);

        return Inertia::render('Assets/Providers/Form', [
            'provider' => [
                ...$provider->toArray(),
                'type' => $provider->getRawOriginal('type'),
                'status' => $provider->getRawOriginal('status'),
            ],
            'types' => ProviderType::options(),
            'statuses' => ProviderStatus::options(),
        ]);
    }

    public function update(UpsertProviderRequest $request, Provider $provider, UpsertProvider $action): RedirectResponse
    {
        $action->handle($provider, $request->validated());

        return redirect()->route('providers.index')->with('success', 'Provider updated.');
    }

    public function destroy(Provider $provider, DeleteProvider $action): RedirectResponse
    {
        $this->authorize('delete', $provider);
        $action->handle($provider);

        return redirect()->route('providers.index')->with('success', 'Provider deleted.');
    }
}
