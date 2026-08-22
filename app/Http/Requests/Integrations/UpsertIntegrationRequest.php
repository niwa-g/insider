<?php

namespace App\Http\Requests\Integrations;

use App\Domains\Integrations\Enums\IntegrationCategory;
use App\Domains\Integrations\Enums\IntegrationProvider;
use App\Domains\Integrations\Models\Integration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('integration');

        return $model instanceof Integration
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('integrations.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $integration = $this->route('integration');

        return [
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', Rule::enum(IntegrationProvider::class)],
            'category' => ['nullable', Rule::enum(IntegrationCategory::class)],
            'config' => ['nullable', 'array'],
            'config.region' => ['nullable', 'string', 'max:64'],
            'config.account_id' => ['nullable', 'string', 'max:64'],
            'config.base_url' => ['nullable', 'url', 'max:255'],
            'config.org' => ['nullable', 'string', 'max:128'],
            'config.login' => ['nullable', 'string', 'max:128'],
            'config.zone_id' => ['nullable', 'string', 'max:128'],
            'is_enabled' => ['nullable', 'boolean'],
            'credentials' => ['nullable', 'array'],
            'credentials.access_key' => ['nullable', 'string', 'max:255'],
            'credentials.secret_key' => ['nullable', 'string', 'max:255'],
            'credentials.token' => ['nullable', 'string', 'max:512'],
            'credentials.api_key' => ['nullable', 'string', 'max:512'],
            'credentials.api_secret' => ['nullable', 'string', 'max:512'],
        ];
    }

    /** @return array<string, mixed>|null */
    public function credentialsPayload(): ?array
    {
        $credentials = $this->input('credentials');

        if (! is_array($credentials)) {
            return null;
        }

        return array_filter($credentials, fn ($value) => filled($value));
    }
}
