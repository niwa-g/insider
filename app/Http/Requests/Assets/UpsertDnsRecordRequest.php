<?php

namespace App\Http\Requests\Assets;

use App\Domains\Assets\Enums\DnsRecordStatus;
use App\Domains\Assets\Enums\DnsRecordType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertDnsRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $model = $this->route('dns_record');

        return $model
            ? ($this->user()?->can('update', $model) ?? false)
            : ($this->user()?->can('domains.create') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'domain_id' => ['required', 'ulid', 'exists:domains,id'],
            'type' => ['required', Rule::enum(DnsRecordType::class)],
            'name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string'],
            'ttl' => ['nullable', 'integer', 'min:60'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(DnsRecordStatus::class)],
        ];
    }
}
