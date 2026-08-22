<?php

namespace App\Http\Requests\Assets;

use App\Domains\Assets\Enums\HostingAccountStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertHostingAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('hosting_account');

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
            'plan' => ['nullable', 'string', 'max:255'],
            'primary_domain' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(HostingAccountStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
