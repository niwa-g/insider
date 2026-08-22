<?php

namespace App\Http\Requests\Assets;

use App\Domains\Assets\Enums\CloudResourceStatus;
use App\Domains\Assets\Enums\CloudResourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertCloudResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('cloud_resource');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('infrastructure.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'ulid', 'exists:clients,id'],
            'provider_id' => ['nullable', 'ulid', 'exists:providers,id'],
            'name' => ['required', 'string', 'max:255'],
            'resource_type' => ['required', Rule::enum(CloudResourceType::class)],
            'region' => ['nullable', 'string', 'max:100'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'monthly_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(CloudResourceStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
