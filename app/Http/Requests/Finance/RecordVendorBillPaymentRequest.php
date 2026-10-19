<?php

namespace App\Http\Requests\Finance;

use App\Domains\Finance\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordVendorBillPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $bill = $this->route('vendor_bill');

        return $this->user()?->can('update', $bill) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'paid_on' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
