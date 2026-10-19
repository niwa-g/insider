<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $vendor = $this->route('vendor');

        return $vendor
            ? ($this->user()?->can('update', $vendor) ?? false)
            : ($this->user()?->can('create', \App\Domains\Finance\Models\Vendor::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $orgId = $this->user()?->organization_id;
        $vendorId = $this->route('vendor')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('vendors', 'code')
                    ->where(fn ($q) => $q->where('organization_id', $orgId))
                    ->ignore($vendorId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
