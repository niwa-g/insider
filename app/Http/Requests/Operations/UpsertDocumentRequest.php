<?php

namespace App\Http\Requests\Operations;

use App\Domains\Operations\Enums\DocumentStatus;
use App\Domains\Operations\Enums\DocumentVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('document');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('documents.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isCreate = $this->route('document') === null;

        return [
            'client_id' => ['nullable', 'ulid', 'exists:clients,id'],
            'project_id' => ['nullable', 'ulid', 'exists:projects,id'],
            'ticket_id' => ['nullable', 'ulid', 'exists:tickets,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visibility' => ['required', Rule::enum(DocumentVisibility::class)],
            'status' => ['required', Rule::enum(DocumentStatus::class)],
            'file' => [$isCreate ? 'required' : 'nullable', 'file', 'max:10240'],
        ];
    }
}
