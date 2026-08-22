<?php

namespace App\Http\Requests\Catalog;

use App\Domains\Catalog\Enums\ServiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('service_category');

        return $category
            ? ($this->user()?->can('update', $category) ?? false)
            : ($this->user()?->can('service_catalog.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(ServiceStatus::class)],
        ];
    }
}
