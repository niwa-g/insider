<?php

namespace App\Http\Requests\Delivery;

use Illuminate\Foundation\Http\FormRequest;

class SyncProjectServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()?->can('update', $project) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_service_ids' => ['required', 'array'],
            'client_service_ids.*' => ['ulid', 'exists:client_services,id'],
        ];
    }
}
