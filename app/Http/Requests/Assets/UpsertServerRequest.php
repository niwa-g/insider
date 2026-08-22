<?php

namespace App\Http\Requests\Assets;

use App\Domains\Assets\Enums\ServerRole;
use App\Domains\Assets\Enums\ServerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertServerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('server');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('infrastructure.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'ulid', 'exists:clients,id'],
            'provider_id' => ['nullable', 'ulid', 'exists:providers,id'],
            'name' => ['required', 'string', 'max:255'],
            'hostname' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'role' => ['nullable', Rule::enum(ServerRole::class)],
            'os' => ['nullable', 'string', 'max:255'],
            'specs' => ['nullable', 'array'],
            'status' => ['required', Rule::enum(ServerStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
