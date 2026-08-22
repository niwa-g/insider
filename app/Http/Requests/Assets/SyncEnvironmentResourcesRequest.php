<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class SyncEnvironmentResourcesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $environment = $this->route('environment');

        return $this->user()?->can('update', $environment) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'resources' => ['required', 'array'],
            'resources.*.type' => ['required', 'in:server,hosting_account,cloud_resource'],
            'resources.*.id' => ['required', 'ulid'],
            'resources.*.role' => ['nullable', 'string', 'max:50'],
            'resources.*.notes' => ['nullable', 'string'],
        ];
    }
}
