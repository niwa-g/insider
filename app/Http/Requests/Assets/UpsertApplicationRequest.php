<?php

namespace App\Http\Requests\Assets;

use App\Domains\Assets\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('application');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('applications.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'ulid', 'exists:clients,id'],
            'project_id' => ['nullable', 'ulid', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'stack' => ['nullable', 'string', 'max:255'],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'owner_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
