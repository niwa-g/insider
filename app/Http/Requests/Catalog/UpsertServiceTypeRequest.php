<?php

namespace App\Http\Requests\Catalog;

use App\Domains\Catalog\Enums\BillingCycle;
use App\Domains\Catalog\Enums\BillingType;
use App\Domains\Catalog\Enums\ServiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = $this->route('service_type');

        return $type
            ? ($this->user()?->can('update', $type) ?? false)
            : ($this->user()?->can('service_catalog.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'service_category_id' => ['required', 'ulid', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'billing_type' => ['required', Rule::enum(BillingType::class)],
            'billing_cycle' => ['nullable', Rule::enum(BillingCycle::class)],
            'default_cost' => ['required', 'numeric', 'min:0'],
            'default_selling_price' => ['required', 'numeric', 'min:0'],
            'sla' => ['nullable', 'string', 'max:255'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::enum(ServiceStatus::class)],
        ];
    }
}
