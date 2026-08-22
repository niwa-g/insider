<?php

namespace App\Http\Requests\Operations;

use App\Domains\Operations\Enums\KnowledgeArticleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertKnowledgeArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('knowledge_article');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('knowledge.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $articleId = $this->route('knowledge_article')?->id;

        return [
            'knowledge_category_id' => ['nullable', 'ulid', 'exists:knowledge_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255',
                Rule::unique('knowledge_articles', 'slug')
                    ->where('organization_id', $this->user()?->organization_id)
                    ->ignore($articleId),
            ],
            'summary' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::enum(KnowledgeArticleStatus::class)],
            'is_internal' => ['boolean'],
            'author_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
