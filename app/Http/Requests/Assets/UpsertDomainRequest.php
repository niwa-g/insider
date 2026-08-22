<?php

namespace App\Http\Requests\Assets;

use App\Domains\Assets\Enums\DomainStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('domain');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('domains.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'ulid', 'exists:clients,id'],
            'project_id' => ['nullable', 'ulid', 'exists:projects,id'],
            'provider_id' => ['nullable', 'ulid', 'exists:providers,id'],
            'name' => ['required', 'string', 'max:255'],
            'registrar' => ['nullable', 'string', 'max:255'],
            'registered_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'auto_renew' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::enum(DomainStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
