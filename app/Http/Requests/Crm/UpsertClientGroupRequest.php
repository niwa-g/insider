<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class UpsertClientGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $group = $this->route('client_group');

        if ($group) {
            return $this->user()?->can('update', $group) ?? false;
        }

        return $this->user()?->can('client_groups.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'client_ids' => ['nullable', 'array'],
            'client_ids.*' => ['ulid', 'exists:clients,id'],
        ];
    }
}
