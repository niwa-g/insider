<?php

namespace App\Http\Requests\Delivery;

use App\Domains\Delivery\Enums\ProjectStatus;
use App\Domains\Delivery\Enums\TaskPriority;
use App\Domains\Delivery\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertProjectTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $template = $this->route('project_template');

        return $template
            ? ($this->user()?->can('update', $template) ?? false)
            : ($this->user()?->can('project_templates.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'default_status' => ['required', Rule::enum(ProjectStatus::class)],
            'is_active' => ['sometimes', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.status' => ['nullable', Rule::enum(TaskStatus::class)],
            'items.*.priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'items.*.estimate_hours' => ['nullable', 'integer', 'min:0'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
