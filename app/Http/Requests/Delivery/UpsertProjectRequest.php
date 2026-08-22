<?php

namespace App\Http\Requests\Delivery;

use App\Domains\Delivery\Enums\ProjectPriority;
use App\Domains\Delivery\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project
            ? ($this->user()?->can('update', $project) ?? false)
            : ($this->user()?->can('projects.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'ulid', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'priority' => ['required', Rule::enum(ProjectPriority::class)],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'project_manager_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'project_template_id' => ['nullable', 'ulid', 'exists:project_templates,id'],
            'client_service_ids' => ['nullable', 'array'],
            'client_service_ids.*' => ['ulid', 'exists:client_services,id'],
            'apply_template' => ['sometimes', 'boolean'],
        ];
    }
}
