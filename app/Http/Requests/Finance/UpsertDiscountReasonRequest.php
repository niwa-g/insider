<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertDiscountReasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $reason = $this->route('discount_reason');

        return $reason
            ? ($this->user()?->can('update', $reason) ?? false)
            : ($this->user()?->can('create', \App\Domains\Finance\Models\DiscountReason::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $orgId = $this->user()?->organization_id;
        $reasonId = $this->route('discount_reason')?->id ?? $this->route('discount_reason');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('discount_reasons', 'code')
                    ->where(fn ($q) => $q->where('organization_id', $orgId))
                    ->ignore($reasonId),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
