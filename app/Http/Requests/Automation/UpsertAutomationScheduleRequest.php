<?php

namespace App\Http\Requests\Automation;

use App\Domains\Automation\Enums\AutomationScheduleJobType;
use App\Domains\Automation\Enums\ScheduleFrequency;
use App\Domains\Automation\Models\AutomationSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertAutomationScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('automation_schedule');

        return $model instanceof AutomationSchedule
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('automation.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'job_type' => ['required', Rule::enum(AutomationScheduleJobType::class)],
            'frequency' => ['required', Rule::enum(ScheduleFrequency::class)],
            'config' => ['nullable', 'array'],
            'config.renewal_days' => ['nullable', 'array'],
            'config.renewal_days.*' => ['integer', 'min:1', 'max:365'],
            'is_enabled' => ['nullable', 'boolean'],
        ];
    }
}
