<?php

namespace App\Http\Requests\Assets;

use App\Domains\Assets\Enums\DeploymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertDeploymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('deployment');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('deployments.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'environment_id' => ['required', 'ulid', 'exists:environments,id'],
            'version' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::enum(DeploymentStatus::class)],
            'deployed_at' => ['nullable', 'date'],
            'deployed_by' => ['nullable', 'exists:users,id'],
            'commit_sha' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
