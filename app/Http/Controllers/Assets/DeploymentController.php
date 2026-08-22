<?php

namespace App\Http\Controllers\Assets;

use App\Domains\Assets\Actions\DeleteDeployment;
use App\Domains\Assets\Actions\UpsertDeployment;
use App\Domains\Assets\Enums\DeploymentStatus;
use App\Domains\Assets\Models\Application;
use App\Domains\Assets\Models\Deployment;
use App\Domains\Assets\Models\Environment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\UpsertDeploymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeploymentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Deployment::class);

        $deployments = Deployment::query()
            ->with(['environment:id,name,application_id', 'environment.application:id,name', 'deployer:id,name'])
            ->when($request->string('environment_id')->toString(), fn ($q, $id) => $q->where('environment_id', $id))
            ->when($request->string('application_id')->toString(), function ($q, $appId) {
                $q->whereHas('environment', fn ($eq) => $eq->where('application_id', $appId));
            })
            ->latest('deployed_at')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Assets/Deployments/Index', [
            'deployments' => $deployments,
            'filters' => [
                'environment_id' => $request->string('environment_id')->toString(),
                'application_id' => $request->string('application_id')->toString(),
            ],
            'statuses' => DeploymentStatus::options(),
            'environments' => Environment::query()->with('application:id,name')->orderBy('name')->get(),
            'applications' => Application::query()->orderBy('name')->get(['id', 'name']),
            'can' => [
                'create' => $request->user()?->can('deployments.create') ?? false,
            ],
        ]);
    }

    public function store(UpsertDeploymentRequest $request, UpsertDeployment $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        if (($data['status'] ?? '') === 'success' && empty($data['deployed_at'])) {
            $data['deployed_at'] = now();
            $data['deployed_by'] = $data['deployed_by'] ?? $request->user()->id;
        }
        $action->handle(null, $data);

        return back()->with('success', 'Deployment recorded.');
    }

    public function destroy(Deployment $deployment, DeleteDeployment $action): RedirectResponse
    {
        $this->authorize('delete', $deployment);
        $action->handle($deployment);

        return back()->with('success', 'Deployment deleted.');
    }
}
