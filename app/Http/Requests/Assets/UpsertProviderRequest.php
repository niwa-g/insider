<?php

namespace App\Http\Requests\Assets;

use App\Domains\Assets\Enums\ProviderStatus;
use App\Domains\Assets\Enums\ProviderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('provider');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('providers.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'type' => ['required', Rule::enum(ProviderType::class)],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::enum(ProviderStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
