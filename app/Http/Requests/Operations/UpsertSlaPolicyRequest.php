<?php

namespace App\Http\Requests\Operations;

use App\Domains\Operations\Enums\SlaPolicyStatus;
use App\Domains\Operations\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertSlaPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('sla_policy');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('sla.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_default' => ['boolean'],
            'status' => ['required', Rule::enum(SlaPolicyStatus::class)],
            'targets' => ['array'],
            'targets.*.priority' => ['required', Rule::enum(TicketPriority::class)],
            'targets.*.respond_within_minutes' => ['required', 'integer', 'min:1'],
            'targets.*.resolve_within_minutes' => ['required', 'integer', 'min:1'],
        ];
    }
}
