<?php

namespace App\Http\Requests\Crm;

use App\Domains\Crm\Enums\ContactStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('contacts.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'ulid', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['boolean'],
            'is_billing' => ['boolean'],
            'is_technical' => ['boolean'],
            'status' => ['required', Rule::enum(ContactStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
