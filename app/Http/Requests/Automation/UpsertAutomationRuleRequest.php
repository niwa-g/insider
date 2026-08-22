<?php

namespace App\Http\Requests\Automation;

use App\Domains\Automation\Enums\AutomationTrigger;
use App\Domains\Automation\Models\AutomationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertAutomationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('automation_rule');

        return $model instanceof AutomationRule
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('automation.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'trigger' => ['required', Rule::enum(AutomationTrigger::class)],
            'conditions' => ['nullable', 'array'],
            'conditions.days_ahead' => ['nullable', 'integer', 'min:1', 'max:365'],
            'conditions.priorities' => ['nullable', 'array'],
            'conditions.priorities.*' => ['string', 'in:low,medium,high,urgent'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*.type' => ['required', 'string', 'in:notify_users,notify_assignee,notify_user_ids,notify_owner'],
            'actions.*.role' => ['nullable', 'string', 'max:64'],
            'actions.*.user_ids' => ['nullable', 'array'],
            'notify_in_app' => ['nullable', 'boolean'],
            'notify_email' => ['nullable', 'boolean'],
            'is_enabled' => ['nullable', 'boolean'],
        ];
    }
}
