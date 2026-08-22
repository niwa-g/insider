<?php

namespace App\Http\Requests\Assets;

use App\Domains\Assets\Enums\EnvironmentSlug;
use App\Domains\Assets\Enums\EnvironmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertEnvironmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('environment');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('environments.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'application_id' => ['required', 'ulid', 'exists:applications,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', Rule::enum(EnvironmentSlug::class)],
            'url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::enum(EnvironmentStatus::class)],
            'branch' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
