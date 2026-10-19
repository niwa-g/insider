<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('teams.update') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $orgId = $this->user()?->organization_id;
        $teamId = $this->route('team')?->id ?? $this->route('team');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('teams', 'code')
                    ->where(fn ($q) => $q->where('organization_id', $orgId))
                    ->ignore($teamId),
            ],
            'department_id' => [
                'nullable',
                'string',
                Rule::exists('departments', 'id')->where(fn ($q) => $q->where('organization_id', $orgId)),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('organization_id', $orgId)),
            ],
        ];
    }
}
