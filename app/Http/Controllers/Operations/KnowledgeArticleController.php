<?php

namespace App\Http\Controllers\Operations;

use App\Domains\Operations\Actions\DeleteKnowledgeArticle;
use App\Domains\Operations\Actions\UpsertKnowledgeArticle;
use App\Domains\Operations\Enums\KnowledgeArticleStatus;
use App\Domains\Operations\Models\KnowledgeArticle;
use App\Domains\Operations\Models\KnowledgeCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\UpsertKnowledgeArticleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeArticleController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', KnowledgeArticle::class);

        $articles = KnowledgeArticle::query()
            ->with(['category:id,name', 'author:id,name'])
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where('title', 'ilike', "%{$s}%"))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Operations/KnowledgeArticles/Index', [
            'articles' => $articles,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'statuses' => KnowledgeArticleStatus::options(),
            'can' => [
                'create' => $request->user()?->can('knowledge.create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', KnowledgeArticle::class);

        return Inertia::render('Operations/KnowledgeArticles/Form', [
            'article' => null,
            'categories' => KnowledgeCategory::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => KnowledgeArticleStatus::options(),
            'authors' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(UpsertKnowledgeArticleRequest $request, UpsertKnowledgeArticle $action): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        if (empty($data['author_id'])) {
            $data['author_id'] = $request->user()->id;
        }
        $article = $action->handle(null, $data);

        return redirect()->route('knowledge-articles.show', $article)->with('success', 'Article created.');
    }

    public function show(Request $request, KnowledgeArticle $knowledge_article): Response
    {
        $this->authorize('view', $knowledge_article);

        $knowledge_article->load(['category:id,name', 'author:id,name']);

        return Inertia::render('Operations/KnowledgeArticles/Show', [
            'article' => [
                ...$knowledge_article->toArray(),
                'status' => $knowledge_article->getRawOriginal('status'),
            ],
            'can' => [
                'update' => $request->user()?->can('update', $knowledge_article) ?? false,
                'delete' => $request->user()?->can('delete', $knowledge_article) ?? false,
            ],
        ]);
    }

    public function edit(Request $request, KnowledgeArticle $knowledge_article): Response
    {
        $this->authorize('update', $knowledge_article);

        return Inertia::render('Operations/KnowledgeArticles/Form', [
            'article' => [
                ...$knowledge_article->toArray(),
                'status' => $knowledge_article->getRawOriginal('status'),
            ],
            'categories' => KnowledgeCategory::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => KnowledgeArticleStatus::options(),
            'authors' => User::query()
                ->where('organization_id', $request->user()?->organization_id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(UpsertKnowledgeArticleRequest $request, KnowledgeArticle $knowledge_article, UpsertKnowledgeArticle $action): RedirectResponse
    {
        $action->handle($knowledge_article, $request->validated());

        return redirect()->route('knowledge-articles.show', $knowledge_article)->with('success', 'Article updated.');
    }

    public function destroy(KnowledgeArticle $knowledge_article, DeleteKnowledgeArticle $action): RedirectResponse
    {
        $this->authorize('delete', $knowledge_article);
        $action->handle($knowledge_article);

        return redirect()->route('knowledge-articles.index')->with('success', 'Article deleted.');
    }
}
