<?php

namespace App\Http\Controllers\Operations;

use App\Domains\Operations\Actions\DeleteKnowledgeCategory;
use App\Domains\Operations\Actions\UpsertKnowledgeCategory;
use App\Domains\Operations\Enums\KnowledgeCategoryStatus;
use App\Domains\Operations\Models\KnowledgeCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\UpsertKnowledgeCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', KnowledgeCategory::class);

        $categories = KnowledgeCategory::query()
            ->with('parent:id,name')
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Operations/KnowledgeCategories/Index', [
            'categories' => $categories,
            'filters' => ['search' => $request->string('search')->toString()],
            'statuses' => KnowledgeCategoryStatus::options(),
            'can' => [
                'create' => $request->user()?->can('knowledge.create') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', KnowledgeCategory::class);

        return Inertia::render('Operations/KnowledgeCategories/Form', [
            'category' => null,
            'parents' => KnowledgeCategory::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => KnowledgeCategoryStatus::options(),
        ]);
    }

    public function store(UpsertKnowledgeCategoryRequest $request, UpsertKnowledgeCategory $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $action->handle(null, $data);

        return redirect()->route('knowledge-categories.index')->with('success', 'Category created.');
    }

    public function edit(KnowledgeCategory $knowledge_category): Response
    {
        $this->authorize('update', $knowledge_category);

        return Inertia::render('Operations/KnowledgeCategories/Form', [
            'category' => [
                ...$knowledge_category->toArray(),
                'status' => $knowledge_category->getRawOriginal('status'),
            ],
            'parents' => KnowledgeCategory::query()
                ->where('id', '!=', $knowledge_category->id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses' => KnowledgeCategoryStatus::options(),
        ]);
    }

    public function update(UpsertKnowledgeCategoryRequest $request, KnowledgeCategory $knowledge_category, UpsertKnowledgeCategory $action): RedirectResponse
    {
        $action->handle($knowledge_category, $request->validated());

        return redirect()->route('knowledge-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(KnowledgeCategory $knowledge_category, DeleteKnowledgeCategory $action): RedirectResponse
    {
        $this->authorize('delete', $knowledge_category);
        $action->handle($knowledge_category);

        return redirect()->route('knowledge-categories.index')->with('success', 'Category deleted.');
    }
}
