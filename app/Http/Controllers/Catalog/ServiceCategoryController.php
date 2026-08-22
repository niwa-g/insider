<?php

namespace App\Http\Controllers\Catalog;

use App\Domains\Catalog\Actions\UpsertServiceCategory;
use App\Domains\Catalog\Enums\ServiceStatus;
use App\Domains\Catalog\Models\ServiceCategory;
use App\Domains\Platform\Services\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\UpsertServiceCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ServiceCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ServiceCategory::class);

        return Inertia::render('Catalog/ServiceCategories/Index', [
            'categories' => ServiceCategory::query()
                ->withCount('serviceTypes')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20),
            'can' => [
                'create' => $request->user()?->can('service_catalog.create') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ServiceCategory::class);

        return Inertia::render('Catalog/ServiceCategories/Form', [
            'category' => null,
            'statuses' => ServiceStatus::options(),
        ]);
    }

    public function store(UpsertServiceCategoryRequest $request, UpsertServiceCategory $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('service-categories.index')->with('success', 'Category created.');
    }

    public function edit(ServiceCategory $serviceCategory): Response
    {
        $this->authorize('update', $serviceCategory);

        return Inertia::render('Catalog/ServiceCategories/Form', [
            'category' => [
                ...$serviceCategory->toArray(),
                'status' => $serviceCategory->getRawOriginal('status'),
            ],
            'statuses' => ServiceStatus::options(),
        ]);
    }

    public function update(
        UpsertServiceCategoryRequest $request,
        ServiceCategory $serviceCategory,
        UpsertServiceCategory $action
    ): RedirectResponse {
        $action->handle($serviceCategory, $request->validated());

        return redirect()->route('service-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ServiceCategory $serviceCategory, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $serviceCategory);

        DB::transaction(function () use ($serviceCategory, $auditLogger) {
            $snapshot = $serviceCategory->toArray();
            $serviceCategory->delete();
            $auditLogger->log('service_category.deleted', $serviceCategory, $snapshot, null);
        });

        return redirect()->route('service-categories.index')->with('success', 'Category deleted.');
    }
}
