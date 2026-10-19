<?php

namespace App\Http\Requests\Finance;

use App\Domains\Finance\Enums\FinancialEntryCategory;
use App\Domains\Finance\Enums\FinancialEntryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertFinancialEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $entry = $this->route('financial_entry');

        return $entry
            ? ($this->user()?->can('update', $entry) ?? false)
            : ($this->user()?->can('financial_entries.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'ulid', 'exists:clients,id'],
            'project_id' => ['nullable', 'ulid'],
            'client_service_id' => ['nullable', 'ulid', 'exists:client_services,id'],
            'entry_type' => ['required', Rule::enum(FinancialEntryType::class)],
            'category' => ['nullable', Rule::enum(FinancialEntryCategory::class)],
            'vendor' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'occurred_on' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
