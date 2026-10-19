<?php

namespace App\Http\Requests\Finance;

use App\Domains\Finance\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $invoice
            ? ($this->user()?->can('update', $invoice) ?? false)
            : ($this->user()?->can('invoices.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $orgId = $this->user()?->organization_id;
        $discountValue = (float) $this->input('discount_value', 0);
        $isPercentage = $this->input('discount_type') === DiscountType::Percentage->value;

        return [
            'client_id' => ['required', 'ulid', 'exists:clients,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'discount_type' => ['required', Rule::enum(DiscountType::class)],
            'discount_value' => [
                'nullable',
                'numeric',
                'min:0',
                $isPercentage ? 'max:100' : 'max:999999999',
            ],
            'discount_reason_id' => [
                $discountValue > 0 ? 'required' : 'nullable',
                'ulid',
                Rule::exists('discount_reasons', 'id')->where(fn ($q) => $q
                    ->where('organization_id', $orgId)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')),
            ],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.client_service_id' => ['nullable', 'ulid', 'exists:client_services,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'discount_type' => $this->input('discount_type', DiscountType::Flat->value),
            'discount_value' => $this->input('discount_value', 0),
        ]);

        if ((float) $this->input('discount_value', 0) <= 0) {
            $this->merge(['discount_reason_id' => null]);
        }
    }
}
