<?php

namespace App\Http\Requests\Operations;

use App\Domains\Operations\Enums\TicketPriority;
use App\Domains\Operations\Enums\TicketStatus;
use App\Domains\Operations\Enums\TicketType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('ticket');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('tickets.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'ulid', 'exists:clients,id'],
            'project_id' => ['nullable', 'ulid', 'exists:projects,id'],
            'application_id' => ['nullable', 'ulid', 'exists:applications,id'],
            'sla_policy_id' => ['nullable', 'ulid', 'exists:sla_policies,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'type' => ['required', Rule::enum(TicketType::class)],
            'requester_id' => ['nullable', 'exists:users,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
