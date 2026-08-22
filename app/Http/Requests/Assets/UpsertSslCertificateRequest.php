<?php

namespace App\Http\Requests\Assets;

use App\Domains\Assets\Enums\SslCertificateStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertSslCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('ssl_certificate');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('domains.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'ulid', 'exists:clients,id'],
            'domain_id' => ['nullable', 'ulid', 'exists:domains,id'],
            'common_name' => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(SslCertificateStatus::class)],
            'auto_renew' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
