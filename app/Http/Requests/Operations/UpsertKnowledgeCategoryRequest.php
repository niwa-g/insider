<?php

namespace App\Http\Requests\Operations;

use App\Domains\Operations\Enums\KnowledgeCategoryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertKnowledgeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('knowledge_category');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('knowledge.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $categoryId = $this->route('knowledge_category')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255',
                Rule::unique('knowledge_categories', 'slug')
                    ->where('organization_id', $this->user()?->organization_id)
                    ->ignore($categoryId),
            ],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'ulid', 'exists:knowledge_categories,id'],
            'sort_order' => ['integer', 'min:0'],
            'status' => ['required', Rule::enum(KnowledgeCategoryStatus::class)],
        ];
    }
}
