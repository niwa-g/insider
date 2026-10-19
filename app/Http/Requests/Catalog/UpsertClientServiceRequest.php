<?php

namespace App\Http\Requests\Catalog;

use App\Domains\Catalog\Enums\BillingCycle;
use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Enums\ClientServiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertClientServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('client_service');

        return $service
            ? ($this->user()?->can('update', $service) ?? false)
            : ($this->user()?->can('client_services.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'ulid', 'exists:clients,id'],
            'service_type_id' => ['required', 'ulid', 'exists:service_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'renewal_date' => ['nullable', 'date'],
            'billing_type' => ['required', Rule::enum(BillingType::class)],
            'billing_cycle' => ['nullable', Rule::enum(BillingCycle::class)],
            'cost' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'provider' => ['nullable', 'string', 'max:255'],
            'vendor_id' => ['nullable', 'ulid', 'exists:vendors,id'],
            'account_manager_id' => ['nullable', 'exists:users,id'],
            'technical_owner_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::enum(ClientServiceStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
